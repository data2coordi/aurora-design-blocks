/**
 * @jest-environment jsdom
 */
import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

// jest-console の警告チェックを無効化
jest.mock('@wordpress/jest-console', () => ({
    disableErrorCheck: jest.fn(),
}));

import * as blocks from '@wordpress/blocks';

// モック定義
jest.mock('@wordpress/blocks', () => ({ registerBlockType: jest.fn() }));

jest.mock('@wordpress/block-editor', () => {
    const React = require('react');
    function InspectorControls({ children }) { return <div data-testid="mock-inspector-controls">{children}</div>; }
    function RichText({ tagName: Tag = 'div', value, onChange, placeholder }) {
        return (
            <Tag
                data-testid="mock-rich-text"
                contentEditable
                suppressContentEditableWarning
                placeholder={placeholder}
                onBlur={e => onChange(e.target.innerText)}
            >{value}</Tag>
        );
    }
    RichText.Content = ({ tagName: Tag = 'div', value }) => <Tag data-testid="mock-rich-text-content">{value}</Tag>;
    function InnerBlocks() { return <div data-testid="mock-inner-blocks">InnerBlocks</div>; }
    InnerBlocks.ButtonBlockAppender = () => <button data-testid="mock-append-btn">+</button>;
    InnerBlocks.Content = () => <div data-testid="mock-inner-content">Content</div>;
    const useBlockProps = Object.assign(
        jest.fn(props => ({ 'data-testid': 'mock-block-props-edit', ...props })),
        { save: jest.fn(props => ({ 'data-testid': 'mock-block-props-save', ...props })) }
    );
    return { InspectorControls, RichText, InnerBlocks, useBlockProps };
});

jest.mock('@wordpress/components', () => ({
    PanelBody: ({ children }) => <div data-testid="mock-panel-body">{children}</div>,
}));

jest.mock('@wordpress/i18n', () => ({ __: text => text }));

// エラー抑制
beforeAll(() => {
    jest.spyOn(console, 'error').mockImplementation(() => { });
    const { disableErrorCheck } = require('@wordpress/jest-console');
    disableErrorCheck();
});

// テスト対象読み込み

require('../../../blocks/tab-block/src/index.js');
const calls = blocks.registerBlockType.mock.calls;

// 出力を分割
const [tabName, tabSettings] = calls.find(call => call[0] === 'aurora-design-blocks/tab');
const [blockName, blockSettings] = calls.find(call => call[0] === 'aurora-design-blocks/tab-block');

describe('aurora-design-blocks/tab', () => {
    it('registerBlockType called with correct name and attributes', () => {
        expect(tabName).toBe('aurora-design-blocks/tab');
        expect(tabSettings).toEqual(expect.objectContaining({
            attributes: expect.objectContaining({ tabTitle: expect.any(Object) }),
            edit: expect.any(Function),
            save: expect.any(Function),
        }));
    });

    describe('edit', () => {
        const setAttributes = jest.fn();
        const attrs = { tabTitle: 'Title' };
        beforeEach(() => setAttributes.mockClear());

        it('renders RichText and updates tabTitle', () => {
            render(tabSettings.edit({ attributes: attrs, setAttributes, className: 'test-class' }));
            const rt = screen.getByTestId('mock-rich-text');
            expect(rt).toBeInTheDocument();
            fireEvent.blur(rt, { target: { innerText: 'New Title' } });
            expect(setAttributes).toHaveBeenCalledWith({ tabTitle: 'New Title' });
        });

        it('renders InnerBlocks placeholder', () => {
            const { container } = render(tabSettings.edit({ attributes: attrs, setAttributes, className: 'test-class' }));
            expect(container.querySelector('.tab-content')).toBeInTheDocument();
            expect(screen.getByTestId('mock-inner-blocks')).toBeInTheDocument();
        });
    });

    describe('save', () => {
        it('renders tabTitle and InnerBlocks.Content', () => {
            const attrs = { tabTitle: 'Saved Title' };
            const { container } = render(tabSettings.save({ attributes: attrs }));
            const title = screen.getByTestId('mock-rich-text-content');
            expect(title).toHaveTextContent('Saved Title');
            expect(container.querySelector('.tab-content')).toBeInTheDocument();
            expect(screen.getByTestId('mock-inner-content')).toBeInTheDocument();
        });
    });
});

describe('aurora-design-blocks/tab-block', () => {
    it('registerBlockType called with correct name', () => {
        expect(blockName).toBe('aurora-design-blocks/tab-block');
        expect(blockSettings.edit).toBeInstanceOf(Function);
        expect(blockSettings.save).toBeInstanceOf(Function);
    });

    describe('edit', () => {
        it('renders InspectorControls and InnerBlocks with template', () => {
            const { container } = render(blockSettings.edit({}));
            expect(screen.getByTestId('mock-inspector-controls')).toBeInTheDocument();
            expect(container.querySelector('.tabs-navigation-editor')).toBeInTheDocument();
            expect(screen.getByTestId('mock-inner-blocks')).toBeInTheDocument();
        });
    });

    describe('save', () => {
        it('renders InnerBlocks.Content inside wrapper', () => {
            const { container } = render(blockSettings.save());
            expect(container.querySelector('.aurora-design-blocks-tabs')).toBeInTheDocument();
            expect(screen.getByTestId('mock-inner-content')).toBeInTheDocument();
        });
    });
});
