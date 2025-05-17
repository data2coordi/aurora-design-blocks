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
    function InspectorControls({ children }) { return <div data-testid="mock-inspector-controls">{children}</div>; }
    function RichText({ value, onChange, placeholder }) {
        return (
            <div
                data-testid="mock-rich-text"
                onBlur={e => onChange(e.target.innerText)}
                placeholder={placeholder}
            >{value}</div>
        );
    }
    RichText.Content = ({ value }) => <div data-testid="mock-rich-text-content">{value}</div>;
    function MediaUpload({ render }) { return <>{render({ open: jest.fn() })}</>; }
    function MediaUploadCheck({ children }) { return <>{children}</>; }
    const useBlockProps = Object.assign(
        jest.fn(props => ({ 'data-testid': 'mock-block-props-edit', ...props })),
        { save: jest.fn(props => ({ 'data-testid': 'mock-block-props-save', ...props })) }
    );
    return { InspectorControls, RichText, MediaUpload, MediaUploadCheck, useBlockProps };
});

jest.mock('@wordpress/components', () => ({
    PanelBody: ({ title, children }) => <div data-testid="mock-panel-body">{children}</div>,
    Button: ({ onClick, children }) => <button data-testid="mock-button" onClick={onClick}>{children}</button>,
    ToggleControl: ({ label, checked, onChange }) => (
        <input
            type="checkbox"
            data-testid="mock-toggle"
            aria-label={label}
            checked={checked}
            onChange={e => onChange(e.target.checked)}
        />
    ),
}));

jest.mock('@wordpress/i18n', () => ({ __: text => text }));

// エラー抑制
beforeAll(() => {
    jest.spyOn(console, 'error').mockImplementation(() => { });
});

// テスト対象読み込み
require('../../../blocks/speech-bubble/src/index.js');
const [blockName, settings] = blocks.registerBlockType.mock.calls[0];

describe('aurora-design-blocks/speech-bubble', () => {
    it('registerBlockType is called with correct name', () => {
        expect(blockName).toBe('aurora-design-blocks/speech-bubble');
    });

    describe('edit', () => {
        const setAttributes = jest.fn();
        const baseAttrs = {
            content: 'Hello',
            imageUrl: '',
            imageAlt: '',
            imageCaption: '',
            backgroundColor: '#fff',
            textColor: '#000',
            reverse: false,
        };
        beforeEach(() => setAttributes.mockClear());

        it('toggles reverse attribute', () => {
            render(settings.edit({ attributes: baseAttrs, setAttributes, className: 'test-class' }));
            const toggle = screen.getByLabelText('Reverse the positions of the image and speech bubble.');
            fireEvent.click(toggle);
            expect(setAttributes).toHaveBeenCalledWith({ reverse: true });
        });

        it('renders select image button when no imageUrl', () => {
            render(settings.edit({ attributes: baseAttrs, setAttributes, className: 'test-class' }));
            expect(screen.getByText('Select image')).toBeInTheDocument();
        });

        it('renders change image and destructive button when imageUrl present', () => {
            const attrs = { ...baseAttrs, imageUrl: 'url.jpg', imageAlt: 'Alt' };
            render(settings.edit({ attributes: attrs, setAttributes, className: 'test-class' }));
            expect(screen.getByText('Change image')).toBeInTheDocument();
        });

        it('renders RichText for content', () => {
            render(settings.edit({ attributes: baseAttrs, setAttributes, className: 'test-class' }));
            expect(screen.getByTestId('mock-rich-text')).toBeInTheDocument();
        });

        it('applies inline styles via useBlockProps', () => {
            const attrs = { ...baseAttrs, textColor: '#123456', backgroundColor: 'linear-gradient(red, blue)' };
            const { container } = render(settings.edit({ attributes: attrs, setAttributes, className: 'test-class' }));
            const div = container.querySelector('[data-testid="mock-block-props-edit"]');
            expect(div).toHaveStyle('color: #123456');
            expect(div).toHaveStyle('backgroundColor: linear-gradient(red, blue)');
        });
    });

    describe('save', () => {
        it('renders RichText.Content and applies styles', () => {
            const attrs = {
                content: 'Bye',
                imageUrl: 'url.jpg',
                imageAlt: 'Alt',
                imageCaption: 'Caption',
                backgroundColor: '#000',
                textColor: '#fff',
                reverse: true,
            };
            const { container } = render(settings.save({ attributes: attrs }));
            const contents = screen.getAllByTestId('mock-rich-text-content');
            expect(contents.length).toBeGreaterThanOrEqual(1);
            const div = container.querySelector('[data-testid="mock-block-props-save"]');
            expect(div).toHaveStyle('color: #fff');
            expect(div).toHaveStyle('backgroundColor: #000');
        });
    });
});
