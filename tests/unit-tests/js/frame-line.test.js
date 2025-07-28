/**
 * @jest-environment jsdom
 */
import React from 'react';
import { render, screen, cleanup, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import * as blocks from '@wordpress/blocks';

// WordPress 依存をモック
jest.mock('@wordpress/blocks', () => ({
    registerBlockType: jest.fn(),
}));

jest.mock('@wordpress/block-editor', () => {
    const React = require('react');

    function InnerBlocks({ renderAppender }) {
        return (
            <div data-testid="mock-inner-blocks">
                {renderAppender ? renderAppender() : <InnerBlocks.ButtonBlockAppender />}
            </div>
        );
    }
    InnerBlocks.ButtonBlockAppender = () => <button data-testid="mock-append-btn">+</button>;
    InnerBlocks.Content = () => <div data-testid="mock-inner-content">InnerBlocks Content</div>;

    const RichText = ({ tagName, className, placeholder, value, onChange, style }) => {
        const Tag = tagName || 'div';
        const ref = React.useRef(null);

        React.useEffect(() => {
            if (ref.current && ref.current.textContent !== value) {
                ref.current.textContent = value || '';
            }
        }, [value]);

        return (
            <Tag
                ref={ref}
                data-testid="mock-richtext"
                className={className}
                placeholder={placeholder}
                style={style}
                onInput={e => onChange(e.target.innerText)}
                contentEditable
                suppressContentEditableWarning={true}
            />
        );
    };
    RichText.Content = ({ tagName, className, value, style }) => {
        const Tag = tagName || 'div';
        return (
            <Tag
                data-testid="mock-richtext-content"
                className={className}
                style={style}
                dangerouslySetInnerHTML={{ __html: value || '' }}
            />
        );
    };

    return {
        InspectorControls: ({ children }) => <div data-testid="mock-inspector-controls">{children}</div>,
        InnerBlocks,
        RichText,
        useBlockProps: Object.assign(
            jest.fn(props => {
                const baseClassName = props.className || '';
                const alignClass = props.align ? `align${props.align}` : '';
                return {
                    ...props,
                    className: [baseClassName, alignClass].filter(Boolean).join(' ').trim(),
                    'data-testid': 'mock-block-props-edit'
                };
            }),
            {
                save: jest.fn(props => {
                    const baseClassName = props.className || '';
                    const alignClass = props.align ? `align${props.align}` : '';
                    return {
                        ...props,
                        className: [baseClassName, alignClass].filter(Boolean).join(' ').trim(),
                        'data-testid': 'mock-block-props-save'
                    };
                })
            }
        ),
        PanelColorSettings: ({ title, colorSettings }) => (
            <div data-testid="mock-panel-color-settings" aria-label={title}>
                {colorSettings.map((setting, index) => (
                    <div key={index} data-testid={`mock-color-setting-${index}`}>
                        <label>{setting.label}</label>
                        <input
                            type="color"
                            value={setting.value || ''}
                            onChange={e => setting.onChange(e.target.value)}
                            data-testid={`mock-color-input-${index}`}
                            aria-label={setting.label}
                        />
                    </div>
                ))}
            </div>
        ),
    };
});

jest.mock('@wordpress/components', () => ({
    PanelBody: ({ title, children }) => <div data-testid="mock-panel-body" aria-label={title}>{children}</div>,
    SelectControl: ({ value, options, onChange, label }) => (
        <select
            data-testid="mock-select-control"
            value={value}
            onChange={e => onChange(e.target.value)}
            aria-label={label}
        >
            {options.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
        </select>
    ),
    __experimentalNumberControl: ({ value, onChange, label, min }) => (
        <input
            data-testid="mock-number-control"
            type="number"
            value={typeof value === 'number' && !isNaN(value) ? value : ''}
            onChange={e => onChange(parseInt(e.target.value, 10))}
            aria-label={label}
            min={min}
        />
    ),
    TextControl: ({ value, onChange, label }) => (
        <input
            data-testid="mock-text-control"
            type="text"
            value={value}
            onChange={e => onChange(e.target.value)}
            aria-label={label}
        />
    ),
}));

jest.mock('@wordpress/i18n', () => ({ __: text => text }));

// テスト対象モジュールをモック後に読み込む
// 実際のブロックのパスに置き換えてください
require('../../../blocks/frame-line/src/index.js');
const [, settings] = blocks.registerBlockType.mock.calls[0];

describe('aurora-design-blocks/frame-line - FL-002', () => {
    const setAttributes = jest.fn();

    beforeEach(() => {
        setAttributes.mockClear();
        cleanup(); // 各テストの前にDOMをクリーンアップ
    });

    // FL-002: title 属性の更新とレンダリング
    it('title 属性が更新され、RichText に正しく表示される', () => {
        const { rerender } = render(settings.edit({ // 初期レンダリング
            attributes: { title: '', frameLineAlign: 'center' },
            setAttributes,
        }));
        const titleRichText = screen.getByTestId('mock-richtext');
        fireEvent.input(titleRichText, { target: { innerText: 'My Awesome Title' } });
        expect(setAttributes).toHaveBeenCalledWith({ title: 'My Awesome Title' });

        // 属性が更新された状態で再レンダリングをシミュレート
        rerender(settings.edit({
            attributes: { title: 'My Awesome Title', frameLineAlign: 'center' },
            setAttributes,
        }));
        expect(titleRichText).toHaveTextContent('My Awesome Title');
    });
});
