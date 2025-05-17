/**
 * @jest-environment jsdom
 */
import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';

import * as blocks from '@wordpress/blocks';

// jest-consoleの警告チェックを無効化
jest.mock('@wordpress/jest-console', () => ({
    disableErrorCheck: jest.fn(),
}));

// blocksのモック
jest.mock('@wordpress/blocks', () => ({
    registerBlockType: jest.fn(),
}));

// block-editorのモック（InnerBlocks.Contentを含む）
jest.mock('@wordpress/block-editor', () => {
    const React = require('react');

    const InnerBlocks = (props) => <div data-testid="mock-inner-blocks" {...props}>InnerBlocks</div>;
    InnerBlocks.Content = () => <div data-testid="mock-inner-blocks-content">InnerBlocks.Content</div>;

    function InspectorControls({ children }) {
        return <div data-testid="mock-inspector-controls">{children}</div>;
    }

    const useBlockProps = Object.assign(
        jest.fn((props) => ({ 'data-testid': 'mock-block-props-edit', ...props })),
        { save: jest.fn((props) => ({ 'data-testid': 'mock-block-props-save', ...props })) }
    );

    return { InnerBlocks, InspectorControls, useBlockProps };
});

// componentsのモック
jest.mock('@wordpress/components', () => ({
    PanelBody: ({ children }) => <div data-testid="mock-panel-body">{children}</div>,
}));

// i18nのモック
jest.mock('@wordpress/i18n', () => ({ __: (text) => text }));

// エラー抑制
beforeAll(() => {
    jest.spyOn(console, 'error').mockImplementation(() => { });
    const { disableErrorCheck } = require('@wordpress/jest-console');
    disableErrorCheck();
});

// テスト対象の読み込み
require('../../../blocks/slider-block/src/index.js');

const calls = blocks.registerBlockType.mock.calls;
const [blockName, blockSettings] = calls.find(call => call[0] === 'aurora-design-blocks/slider-block');

describe('aurora-design-blocks/slider-block', () => {
    it('registerBlockType is called with correct name and edit/save functions', () => {
        expect(blockName).toBe('aurora-design-blocks/slider-block');
        expect(blockSettings.edit).toBeInstanceOf(Function);
        expect(blockSettings.save).toBeInstanceOf(Function);
    });

    describe('edit', () => {
        test('renders InnerBlocks and descriptive text', () => {
            const { container } = render(blockSettings.edit({}));
            expect(screen.getByTestId('mock-inner-blocks')).toBeInTheDocument();
            expect(container.querySelector('.blockSliders-navigation-editor')).toBeInTheDocument();
            expect(container.querySelector('.blockSliders-content-editor')).toBeInTheDocument();
            expect(container).toHaveTextContent('Please create multiple pieces of content. They will be displayed in a slide format when viewed as a website.');
        });
    });

    describe('save', () => {
        test('renders InnerBlocks.Content inside container', () => {
            const { container } = render(blockSettings.save());
            expect(container.querySelector('.blockSliders')).toBeInTheDocument();
            expect(container.querySelector('.blockSliders-content')).toBeInTheDocument();
            expect(screen.getByTestId('mock-inner-blocks-content')).toBeInTheDocument();
            expect(screen.getByTestId('mock-block-props-save')).toBeInTheDocument();
        });
    });
});
