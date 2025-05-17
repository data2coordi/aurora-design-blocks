/**
 * @jest-environment jsdom
 */
import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import * as blocks from '@wordpress/blocks';

// モック定義
jest.mock('@wordpress/blocks', () => ({ registerBlockType: jest.fn() }));

jest.mock('@wordpress/block-editor', () => {
    const React = require('react');
    function InnerBlocks(props) {
        return <div data-testid="mock-inner-blocks" />;
    }
    InnerBlocks.Content = () => <div data-testid="mock-inner-content" />;
    function MediaUpload({ render }) {
        return <>{render({ open: jest.fn() })}</>;
    }
    function MediaUploadCheck({ children }) { return <>{children}</>; }
    function InspectorControls({ children }) { return <div data-testid="mock-inspector-controls">{children}</div>; }
    const useBlockProps = Object.assign(
        jest.fn(props => ({ 'data-testid': 'mock-block-props-edit', ...props })),
        { save: jest.fn(props => ({ 'data-testid': 'mock-block-props-save', ...props })) }
    );
    return { useBlockProps, InspectorControls, InnerBlocks, MediaUpload, MediaUploadCheck };
});

jest.mock('@wordpress/components', () => ({
    PanelBody: ({ title, children }) => <div data-testid="mock-panel-body">{children}</div>,
    ToggleControl: ({ label, checked, onChange }) => (
        <input
            type="checkbox"
            data-testid="mock-toggle"
            aria-label={label}
            checked={checked}
            onChange={e => onChange(e.target.checked)}
        />
    ),
    RangeControl: ({ label, value, onChange, min, max }) => (
        <input
            type="range"
            data-testid="mock-range"
            aria-label={label}
            value={value}
            min={min}
            max={max}
            onChange={e => onChange(Number(e.target.value))}
        />
    ),
    Button: ({ onClick, children }) => <button onClick={onClick}>{children}</button>,
}));

jest.mock('@wordpress/i18n', () => ({ __: text => text }));

// テスト対象読み込み
require('../../../blocks/custom-cover/src/index.js');
const [blockName, settings] = blocks.registerBlockType.mock.calls[0];

describe('aurora-design-blocks/custom-cover', () => {
    it('registerBlockType is called with correct name', () => {
        expect(blockName).toBe('aurora-design-blocks/custom-cover');
    });

    describe('edit', () => {
        const setAttributes = jest.fn();
        const attrs = { innerWidthArticle: false, url: '', id: 0, alt: '', focalPoint: { x: 0.5, y: 0.5 }, dimRatio: 50 };

        beforeEach(() => setAttributes.mockClear());

        it('renders toggle and toggles innerWidthArticle', () => {
            render(settings.edit({ attributes: attrs, setAttributes }));
            const toggle = screen.getByLabelText('Use Article Width for Inner Content');
            expect(toggle.checked).toBe(false);
            fireEvent.click(toggle);
            expect(setAttributes).toHaveBeenCalledWith({ innerWidthArticle: true });
        });

        it('renders range and updates dimRatio', () => {
            render(settings.edit({ attributes: attrs, setAttributes }));
            const range = screen.getByLabelText('Overlay Opacity (-100 for bright, 100 for dark)');
            fireEvent.change(range, { target: { value: '20' } });
            expect(setAttributes).toHaveBeenCalledWith({ dimRatio: 20 });
        });

        it('renders upload and remove buttons conditionally', () => {
            // no url => upload
            render(settings.edit({ attributes: attrs, setAttributes }));
            expect(screen.getByText('Upload Background Image')).toBeInTheDocument();
            // with url => change and remove
            const withUrl = { ...attrs, url: 'a.jpg', id: 1, alt: 'alt' };
            render(settings.edit({ attributes: withUrl, setAttributes }));
            expect(screen.getByText('Change Background Image')).toBeInTheDocument();
            expect(screen.getByText('Remove Background Image')).toBeInTheDocument();
        });

        it('applies overlay color style based on dimRatio', () => {
            const { container } = render(settings.edit({ attributes: attrs, setAttributes }));
            const overlay = container.querySelector('.cover-overlay');
            expect(overlay).not.toBeNull();
            expect(overlay.style.background).toBe('rgba(0, 0, 0, 0.5)');
        });
    });

    describe('save', () => {
        const attrs = { innerWidthArticle: true, url: 'b.jpg', id: 2, alt: 'alt', focalPoint: { x: 0.2, y: 0.4 }, dimRatio: -50 };

        it('renders correct overlay and inner blocks content', () => {
            const { container } = render(settings.save({ attributes: attrs }));
            const overlay = container.querySelector('.cover-overlay');
            expect(overlay.style.background).toBe('rgba(255, 255, 255, 0.5)');
            expect(container.querySelector('.inner-container.inner-article')).not.toBeNull();
            expect(screen.getByTestId('mock-inner-content')).toBeInTheDocument();
        });
    });
});
