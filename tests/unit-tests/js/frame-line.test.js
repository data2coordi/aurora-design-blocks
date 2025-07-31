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

describe('aurora-design-blocks/frame-line title', () => {
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
    it('SelectControl の操作で frameLineAlign が更新されクラスが変わる', () => {
        const { rerender } = render(settings.edit({
            attributes: { frameLineAlign: 'center' },
            setAttributes,
        }));

        const blockPropsElement = screen.getByTestId('mock-block-props-edit');
        expect(blockPropsElement).toHaveClass('frame-line-center');

        // aria-label で特定して取得
        const select = screen.getByLabelText('Frame-line-title Alignment');
        expect(select.value).toBe('center');

        fireEvent.change(select, { target: { value: 'left' } });
        expect(setAttributes).toHaveBeenCalledWith({ frameLineAlign: 'left' });

        rerender(settings.edit({
            attributes: { frameLineAlign: 'left' },
            setAttributes,
        }));

        const updatedBlockPropsElement = screen.getByTestId('mock-block-props-edit');
        expect(updatedBlockPropsElement).toHaveClass('frame-line-left');

        const updatedSelect = screen.getByLabelText('Frame-line-title Alignment');
        expect(updatedSelect.value).toBe('left');
    });

    it('Border Color の操作で title の背景色に borderColor が反映される', () => {
        const { rerender } = render(settings.edit({
            attributes: { frameLineAlign: 'center', title: 'Hello', borderColor: undefined },
            setAttributes,
        }));

        // 初期状態：borderColor 未設定 → white が入っているはず
        const titleRichText = screen.getByTestId('mock-richtext');
        expect(titleRichText).toHaveStyle({ backgroundColor: 'white' });

        // 「Border Color」のカラーピッカーを取得して変更
        const borderColorInput = screen.getByLabelText('Border Color');
        fireEvent.change(borderColorInput, { target: { value: '#123456' } });

        // setAttributes が正しく呼ばれる
        expect(setAttributes).toHaveBeenCalledWith({ borderColor: '#123456' });

        // 属性が更新された想定で再レンダリング
        rerender(settings.edit({
            attributes: { frameLineAlign: 'center', title: 'Hello', borderColor: '#123456' },
            setAttributes,
        }));

        // タイトル背景に反映されていることを検証
        const updatedTitleRichText = screen.getByTestId('mock-richtext');
        expect(updatedTitleRichText).toHaveStyle({ backgroundColor: '#123456' });
    });
    it('Title Text Color の操作で titleColor が文字色として反映される', () => {
        const { rerender } = render(settings.edit({
            attributes: { frameLineAlign: 'center', title: 'Hello', titleColor: undefined },
            setAttributes,
        }));

        // カラーピッカー（Title Text Color）を取得して変更
        const titleTextColorInput = screen.getByLabelText('Title Text Color');
        fireEvent.change(titleTextColorInput, { target: { value: '#ff0000' } });

        // setAttributes が適切に呼ばれる
        expect(setAttributes).toHaveBeenCalledWith({ titleColor: '#ff0000' });

        // 属性更新後として再レンダリング
        rerender(settings.edit({
            attributes: { frameLineAlign: 'center', title: 'Hello', titleColor: '#ff0000' },
            setAttributes,
        }));

        // タイトル（RichText）の文字色に反映されていること
        const titleRichText = screen.getByTestId('mock-richtext');
        expect(titleRichText).toHaveStyle({ color: '#ff0000' });
    });
    it('Title Border Radius の操作で titleBorderRadius がタイトルに反映される（edit）', () => {
        const { rerender } = render(settings.edit({
            attributes: {
                frameLineAlign: 'center',
                title: 'Hello',
                titleBorderRadius: '0px',
            },
            setAttributes,
        }));

        // 初期は 0px のはず
        let titleRichText = screen.getByTestId('mock-richtext');
        expect(titleRichText).toHaveStyle({ borderRadius: '0px' });

        // セレクトを 16px に変更
        const radiusSelect = screen.getByLabelText('Title Border Radius');
        fireEvent.change(radiusSelect, { target: { value: '16px' } });

        // setAttributes が適切に呼ばれる
        expect(setAttributes).toHaveBeenCalledWith({ titleBorderRadius: '16px' });

        // 属性更新後として再レンダリング
        rerender(settings.edit({
            attributes: {
                frameLineAlign: 'center',
                title: 'Hello',
                titleBorderRadius: '16px',
            },
            setAttributes,
        }));

        // タイトルへの反映を確認
        titleRichText = screen.getByTestId('mock-richtext');
        expect(titleRichText).toHaveStyle({ borderRadius: '16px' });
    });



});


describe('aurora-design-blocks/frame-line frame-line', () => {
    const setAttributes = jest.fn();

    beforeEach(() => {
        setAttributes.mockClear();
        cleanup(); // 各テストの前にDOMをクリーンアップ
    });

    it('Background Color の操作で backgroundColor がブロック背景に適用される（edit）', () => {
        const { rerender } = render(settings.edit({
            attributes: { frameLineAlign: 'center', backgroundColor: undefined },
            setAttributes,
        }));

        // 初期は未設定（明示テストは省略）。色を #112233 に変更
        const bgColorInput = screen.getByLabelText('Background Color');
        fireEvent.change(bgColorInput, { target: { value: '#112233' } });

        // setAttributes が正しく呼ばれる
        expect(setAttributes).toHaveBeenCalledWith({ backgroundColor: '#112233' });

        // 属性更新を想定して再レンダリング
        rerender(settings.edit({
            attributes: { frameLineAlign: 'center', backgroundColor: '#112233' },
            setAttributes,
        }));

        // useBlockProps の付いた要素（ブロック本体）に反映されていること
        const block = screen.getByTestId('mock-block-props-edit');
        expect(block).toHaveStyle({ backgroundColor: '#112233' });
    });
    it('Border Style の操作で borderStyle が枠線に適用される（edit）', () => {
        const { rerender } = render(settings.edit({
            attributes: { frameLineAlign: 'center', borderStyle: 'solid' },
            setAttributes,
        }));

        // 初期状態
        let block = screen.getByTestId('mock-block-props-edit');
        expect(block).toHaveStyle({ borderStyle: 'solid' });
        expect(block).toHaveClass('border-solid');

        // セレクトで dashed を選択
        const borderStyleSelect = screen.getByLabelText('Border Style');
        fireEvent.change(borderStyleSelect, { target: { value: 'dashed' } });

        // setAttributes が正しく呼ばれる
        expect(setAttributes).toHaveBeenCalledWith({ borderStyle: 'dashed' });

        // 属性更新を想定して再レンダリング
        rerender(settings.edit({
            attributes: { frameLineAlign: 'center', borderStyle: 'dashed' },
            setAttributes,
        }));

        // 反映確認（style と class 両方）
        block = screen.getByTestId('mock-block-props-edit');
        expect(block).toHaveStyle({ borderStyle: 'dashed' });
        expect(block).toHaveClass('border-dashed');
    });

    it('Border Radius の操作で borderRadius が枠線に適用される（edit）', () => {
        const { rerender } = render(settings.edit({
            attributes: {
                frameLineAlign: 'center',
                borderRadius: '0px', // 初期値
            },
            setAttributes,
        }));

        // 初期確認
        let block = screen.getByTestId('mock-block-props-edit');
        expect(block).toHaveStyle({ borderRadius: '0px' });

        // 「Border Radius (px)」入力を 12 に変更（NumberControl の onChange は数値を渡す）
        const radiusInput = screen.getByLabelText('Border Radius (px)');
        fireEvent.change(radiusInput, { target: { value: '12' } });

        // setAttributes が '12px' で呼ばれる
        expect(setAttributes).toHaveBeenCalledWith({ borderRadius: '12px' });

        // 属性更新を想定して再レンダリング
        rerender(settings.edit({
            attributes: {
                frameLineAlign: 'center',
                borderRadius: '12px',
            },
            setAttributes,
        }));

        // 反映確認
        block = screen.getByTestId('mock-block-props-edit');
        expect(block).toHaveStyle({ borderRadius: '12px' });
    });
    it('edit: 枠線内(.frame-line-content)に InnerBlocks の領域が出力される', () => {
        render(settings.edit({
            attributes: { frameLineAlign: 'center', title: 'Hello' },
            setAttributes,
        }));

        // 枠線コンテナ（useBlockProps が付いたブロック本体）
        const block = screen.getByTestId('mock-block-props-edit');

        // 枠線内のコンテンツ領域
        const contentArea = block.querySelector('.frame-line-content');
        expect(contentArea).toBeInTheDocument();

        // モックの InnerBlocks が入っていること
        const inner = screen.getByTestId('mock-inner-blocks');
        expect(contentArea).toContainElement(inner);

        // さらに、デフォルトの ButtonBlockAppender（+ ボタン）が表示されること
        expect(screen.getByTestId('mock-append-btn')).toBeInTheDocument();
    });

    it('save: 枠線内(.frame-line-content)に InnerBlocks の中身が出力される', () => {
        const view = settings.save({
            attributes: { frameLineAlign: 'center', title: 'Hello' },
        });

        render(view);

        // 保存時のブロック本体
        const saved = screen.getByTestId('mock-block-props-save');

        // 枠線内のコンテンツ領域
        const contentArea = saved.querySelector('.frame-line-content');
        expect(contentArea).toBeInTheDocument();

        // モックの InnerBlocks.Content が入っていること
        const innerContent = screen.getByTestId('mock-inner-content');
        expect(contentArea).toContainElement(innerContent);

        // 文字列を簡易確認（モック実装は "InnerBlocks Content"）
        expect(innerContent).toHaveTextContent('InnerBlocks Content');
    });



});

/*
タイトル
title がHTML内に出力されること
frameLineAlign に応じたクラスが付与される
borderColor が title の背景色として反映される
titleColor が文字色として反映される
titleBorderRadius が反映される

枠線
backgroundColor がブロック背景に適用される
borderColor が枠線に適用される
borderStyle（solid, dashedなど）が適用される
borderWidth が適用される
borderRadius がブロック全体に適用される
InnerBlocks の中身（段落など）が出力される

*/