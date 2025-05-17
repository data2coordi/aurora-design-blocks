/**
 * @jest-environment jsdom
 */
import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import * as blocks from '@wordpress/blocks';

// 1. WordPress 依存をモック
jest.mock('@wordpress/blocks', () => ({
    registerBlockType: jest.fn(),
}));

jest.mock('@wordpress/block-editor', () => {
    const React = require('react');
    function InnerBlocks({ renderAppender }) {
        return <div data-testid="mock-inner-blocks">{renderAppender()}</div>;
    }
    InnerBlocks.ButtonBlockAppender = () => <button data-testid="mock-append-btn">+</button>;
    InnerBlocks.Content = () => <div data-testid="mock-inner-content">CONTENT</div>;

    return {
        InspectorControls: ({ children }) => <div data-testid="mock-inspector-controls">{children}</div>,
        InnerBlocks,
        useBlockProps: Object.assign(
            jest.fn(props => ({ ...props, 'data-testid': 'mock-block-props-edit' })),
            { save: jest.fn(props => ({ ...props, 'data-testid': 'mock-block-props-save' })) }
        ),
    };
});

jest.mock('@wordpress/components', () => ({
    PanelBody: ({ title, children }) => <div data-testid="mock-panel-body" aria-label={title}>{children}</div>,
    ToggleControl: ({ checked, onChange, label }) => (
        <input
            data-testid="mock-toggle"
            type="checkbox"
            checked={checked}
            onChange={e => onChange(e.target.checked)}
            aria-label={label}
        />
    ),
    SelectControl: ({ value, options, onChange, label }) => (
        <select
            data-testid="mock-select"
            value={value}
            onChange={e => onChange(e.target.value)}
            aria-label={label}
        >
            {options.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
        </select>
    ),
}));

jest.mock('@wordpress/i18n', () => ({ __: text => text }));

// 2. テスト対象モジュールをモック後に読み込む
require('../../../blocks/cta-block/src/index.js');
const [blockName, settings] = blocks.registerBlockType.mock.calls[0];

describe('aurora-design-blocks/cta-block', () => {
    it('registerBlockType が正しい name と設定で呼ばれている', () => {
        expect(blockName).toBe('aurora-design-blocks/cta-block');
        expect(settings).toEqual(expect.objectContaining({
            attributes: {
                isFixed: expect.objectContaining({ type: 'boolean', default: true }),
                position: expect.objectContaining({ type: 'string', default: 'bottom-center' }),
            },
            edit: expect.any(Function),
            save: expect.any(Function),
        }));
    });

    describe('edit component', () => {
        const setAttributes = jest.fn();
        beforeEach(() => setAttributes.mockClear());

        it('クラス名とアトリビュートが正しく付与される', () => {
            const { container } = render(settings.edit({
                attributes: { isFixed: true, position: 'bottom-center' },
                setAttributes,
            }));
            const el = container.querySelector('.cta-block.fixed.position-bottom-center');
            expect(el).not.toBeNull();
        });

        it('ToggleControl が onChange で setAttributes を呼ぶ', () => {
            render(settings.edit({
                attributes: { isFixed: true, position: 'bottom-center' },
                setAttributes,
            }));
            const toggle = screen.getByLabelText('Make it a fixed float');
            fireEvent.click(toggle);
            expect(setAttributes).toHaveBeenCalledWith({ isFixed: false });
        });

        it('SelectControl が onChange で setAttributes を呼ぶ', () => {
            render(settings.edit({
                attributes: { isFixed: true, position: 'bottom-center' },
                setAttributes,
            }));
            const select = screen.getByLabelText('CTA Position');
            fireEvent.change(select, { target: { value: 'top-left' } });
            expect(setAttributes).toHaveBeenCalledWith({ position: 'top-left' });
        });
    });

    describe('save component', () => {
        it('クラス名が正しく付与される', () => {
            const { container } = render(settings.save({
                attributes: { isFixed: false, position: 'top-right' },
            }));
            const el = container.querySelector('.cta-block.position-top-right');
            expect(el).not.toBeNull();
        });
    });
});
