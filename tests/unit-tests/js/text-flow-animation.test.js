/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ColorPicker, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Testing-library imports
 */
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

// WordPressのコアパッケージをモックします
// index.jsで実際に使用されているAPIのみをモックします
jest.mock('@wordpress/blocks', () => ({
    registerBlockType: jest.fn(),
}));

jest.mock('@wordpress/block-editor', () => ({
    useBlockProps: Object.assign(
        jest.fn((settings) => ({
            // Editコンポーネント用のモック戻り値
            'data-testid': 'mock-block-props-edit', // テスト用の識別子を追加
            className: `mock-edit-block-props ${settings?.className || ''}`,
            ...settings,
        })),
        {
            save: jest.fn((settings) => ({
                // Saveコンポーネント用のモック戻り値
                'data-testid': 'mock-block-props-save', // テスト用の識別子を追加
                className: `mock-save-block-props ${settings?.className || ''}`,
                ...settings,
            })),
        }
    ),
    // RichTextコンポーネントのモック
    RichText: jest.fn(({ value, onChange, placeholder, tagName, style }) => {
        const Tag = tagName || 'div';
        return (
            <Tag
                data-testid="mock-rich-text" // テスト用の識別子
                contentEditable // 編集可能であることをシミュレート
                onBlur={(e) => onChange(e.target.innerText)} // contentEditableの変更をシミュレート
                placeholder={placeholder}
                style={style}
                // valueはcontentEditable要素ではinnerTextとして扱われることが多い
                dangerouslySetInnerHTML={{ __html: value }}
            />
        );
    }),
    // InspectorControlsコンポーネントのモック
    InspectorControls: jest.fn(({ children }) => (
        <div data-testid="mock-inspector-controls">{children}</div>
    )),
}));

jest.mock('@wordpress/components', () => ({
    // PanelBodyコンポーネントのモック
    PanelBody: jest.fn(({ title, children }) => (
        <div data-testid="mock-panel-body" aria-label={title}>{children}</div>
    )),
    // RangeControlコンポーネントのモック
    RangeControl: jest.fn(({ label, value, onChange, min, max }) => (
        <div data-testid={`mock-range-control-${label}`}>
            <label>{label}</label>
            <input
                type="range" // または number
                value={value}
                min={min}
                max={max}
                onChange={e => onChange(parseFloat(e.target.value))}
                aria-label={label}
            />
        </div>
    )),
    // ColorPickerコンポーネントのモック
    ColorPicker: jest.fn(({ label, color, onChangeComplete }) => (
        <div data-testid={`mock-color-picker-${label}`}>
            <label>{label}</label>
            <input
                type="color" // または text
                value={color}
                onChange={e => onChangeComplete({ hex: e.target.value })}
                aria-label={label}
            />
        </div>
    )),
    // SelectControlコンポーネントのモック
    SelectControl: jest.fn(({ label, value, options, onChange }) => (
        <div data-testid={`mock-select-control-${label}`}>
            <label>{label}</label>
            <select value={value} onChange={e => onChange(e.target.value)} aria-label={label}>
                {options.map(option => (
                    <option key={option.value} value={option.value}>{option.label}</option>
                ))}
            </select>
        </div>
    )),
}));

jest.mock('@wordpress/i18n', () => ({
    __: jest.fn((text) => text), // 翻訳関数はシンプルに元の文字列を返すだけ
}));

// index.jsをrequireして、registerBlockTypeの呼び出しをトリガーし、設定を取得します
let registeredBlockSettings;

describe('TextFlowAnimation Block Registration', () => {
    beforeAll(() => {
        // index.jsをrequireすると、registerBlockTypeが実行されます
        require('../../../blocks/text-flow-animation/src/index.js');
        // registerBlockTypeが呼び出された際の引数を取得します
        registeredBlockSettings = registerBlockType.mock.calls[0][1];
    });

    it('should register the block type with the correct name', () => {
        expect(registerBlockType).toHaveBeenCalledWith(
            'aurora-design-blocks/text-flow-animation', // index.jsでハードコードされているブロック名
            expect.any(Object) // 設定オブジェクトが渡されていることを確認
        );
    });

    it('should register the block type with edit and save functions', () => {
        expect(registeredBlockSettings).toHaveProperty('edit');
        expect(typeof registeredBlockSettings.edit).toBe('function');
        expect(registeredBlockSettings).toHaveProperty('save');
        expect(typeof registeredBlockSettings.save).toBe('function');
    });

    // index.jsにはattributesの定義がありませんが、必要であればここで検証を追加できます
    // 例: expect(registeredBlockSettings).toHaveProperty('attributes');
});

describe('TextFlowAnimation Edit Component (from registered settings)', () => {
    const mockSetAttributes = jest.fn();
    const defaultAttributes = {
        fontSize: 30,
        color: '#000000',
        fontFamily: "Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif",
        content: 'Initial Text',
    };

    beforeEach(() => {
        mockSetAttributes.mockClear();
        // useBlockProps のモックの戻り値を必要に応じて調整します
        useBlockProps.mockReturnValue({
            'data-testid': 'mock-block-props-edit',
            className: 'wp-block-aurora-design-text-flow-animation-edit',
        });
        // __関数のモックをクリア
        __.mockClear();
        __.mockImplementation((text) => text); // テスト中は元の文字列を返す
    });

    it('should render correctly with default attributes', () => {
        render(registeredBlockSettings.edit({ attributes: defaultAttributes, setAttributes: mockSetAttributes }));

        // RichTextがレンダリングされ、属性が反映されているか確認
        const richTextElement = screen.getByTestId('mock-rich-text');
        expect(richTextElement).toBeInTheDocument();
        expect(richTextElement).toHaveStyle(`font-size: ${calculateFontSize(defaultAttributes.fontSize)}`);
        expect(richTextElement).toHaveStyle(`color: ${defaultAttributes.color}`);
        expect(richTextElement).toHaveStyle(`font-family: ${defaultAttributes.fontFamily}`);
        expect(richTextElement).toHaveTextContent(defaultAttributes.content);
        expect(richTextElement).toHaveAttribute('placeholder', 'Enter text...');

        // InspectorControlsがレンダリングされているか確認
        expect(screen.getByTestId('mock-inspector-controls')).toBeInTheDocument();
        expect(screen.getByTestId('mock-panel-body')).toBeInTheDocument();

        // 各コントロールがレンダリングされ、属性が反映されているか確認
        expect(screen.getByLabelText('Font size')).toHaveValue(String(defaultAttributes.fontSize));
        expect(screen.getByLabelText('Color')).toHaveValue(defaultAttributes.color);
        expect(screen.getByLabelText('Font family')).toHaveValue(defaultAttributes.fontFamily);

        // ガイドテキストがレンダリングされているか確認
        expect(screen.getByText('Please enter the scrolling text.')).toBeInTheDocument();
    });

    it('should call setAttributes when RichText content is changed', () => {
        render(registeredBlockSettings.edit({ attributes: defaultAttributes, setAttributes: mockSetAttributes }));
        const richTextElement = screen.getByTestId('mock-rich-text');

        const newContent = 'New Text Input';
        // contentEditable要素の変更をシミュレートするには、innerTextを変更してblurイベントを発生させるのが一般的
        fireEvent.blur(richTextElement, { target: { innerText: newContent } });

        expect(mockSetAttributes).toHaveBeenCalledWith({ content: newContent });
    });

    // 他のコントロールの変更に対する setAttributes の呼び出しテストを追加
    it('should call setAttributes when Font size is changed', () => {
        render(registeredBlockSettings.edit({ attributes: defaultAttributes, setAttributes: mockSetAttributes }));
        const rangeInput = screen.getByLabelText('Font size');
        fireEvent.change(rangeInput, { target: { value: '45' } });
        expect(mockSetAttributes).toHaveBeenCalledWith({ fontSize: 45 });
    });

    it('should call setAttributes when Color is changed', () => {
        render(registeredBlockSettings.edit({ attributes: defaultAttributes, setAttributes: mockSetAttributes }));
        const colorInput = screen.getByLabelText('Color');
        fireEvent.change(colorInput, { target: { value: '#ff0000' } });
        expect(mockSetAttributes).toHaveBeenCalledWith({ color: '#ff0000' });
    });

    it('should call setAttributes when Font family is changed', () => {
        render(registeredBlockSettings.edit({ attributes: defaultAttributes, setAttributes: mockSetAttributes }));
        const selectElement = screen.getByLabelText('Font family');
        const newFontValue = 'Georgia, serif';
        fireEvent.change(selectElement, { target: { value: newFontValue } });
        expect(mockSetAttributes).toHaveBeenCalledWith({ fontFamily: newFontValue });
    });
});

describe('TextFlowAnimation Save Component (from registered settings)', () => {
    const defaultAttributes = {
        fontSize: 35,
        color: '#333333',
        fontFamily: 'Verdana, sans-serif',
        content: 'Saved Content',
    };

    beforeEach(() => {
        // useBlockProps.save() のモックの戻り値を必要に応じて調整します
        // This mock should reflect how useBlockProps.save combines classes.
        // It takes settings from the block's save function (e.g., custom className, style)
        // and merges them with WordPress's standard block class.
        useBlockProps.save.mockImplementation((settingsPassedToUseBlockProps) => {
            const blockGeneratedClassName = 'wp-block-aurora-design-blocks-text-flow-animation';
            const userProvidedClassName = settingsPassedToUseBlockProps?.className || '';
            return {
                'data-testid': 'mock-block-props-save',
                className: `${blockGeneratedClassName} ${userProvidedClassName}`.trim(),
                style: settingsPassedToUseBlockProps?.style, // Pass through style
            };
        });
    });

    it('should render correctly with default attributes', () => {
        render(registeredBlockSettings.save({ attributes: defaultAttributes }));

        const saveElement = screen.getByTestId('mock-block-props-save');
        expect(saveElement).toBeInTheDocument();
        expect(saveElement).toHaveClass('aurora-design-blocks-text-flow-animation'); // 実際のクラス名も確認
        expect(saveElement).toHaveStyle(`font-size: ${calculateFontSize(defaultAttributes.fontSize)}`);
        expect(saveElement).toHaveStyle(`color: ${defaultAttributes.color}`);
        expect(saveElement).toHaveStyle(`font-family: ${defaultAttributes.fontFamily}`);

        // loop_wrap divとその中のコンテンツを確認
        const loopWrap = saveElement.querySelector('.loop_wrap');
        expect(loopWrap).toBeInTheDocument();

        const contentDivs = loopWrap.querySelectorAll('div');
        expect(contentDivs).toHaveLength(3); // 3つのdivがあることを確認
        contentDivs.forEach(div => {
            // &nbsp; も含めてコンテンツを確認
            expect(div.innerHTML).toBe(`${defaultAttributes.content}&nbsp;`);
        });
    });

    // 他の属性値でのレンダリングテストを追加
    it('should render correctly with different attributes', () => {
        const attributes = {
            fontSize: 50,
            color: '#cccccc',
            fontFamily: 'Georgia, serif',
            content: 'Another Saved Content',
        };
        render(registeredBlockSettings.save({ attributes }));

        const saveElement = screen.getByTestId('mock-block-props-save');
        expect(saveElement).toHaveStyle(`font-size: ${calculateFontSize(attributes.fontSize)}`);
        expect(saveElement).toHaveStyle(`color: ${attributes.color}`);
        expect(saveElement).toHaveStyle(`font-family: ${attributes.fontFamily}`);

        const contentDivs = saveElement.querySelectorAll('.loop_wrap div');
        contentDivs.forEach(div => {
            expect(div.innerHTML).toBe(`${attributes.content}&nbsp;`);
        });
    });
});

// calculateFontSize ヘルパー関数は index.js 内で定義されているため、テストファイル内で再定義またはインポートが必要です。
// シンプルなテストのためにここで再定義します。
const calculateFontSize = (fontSize) => {
    const baseSize = (fontSize / 800) * 100;
    return `${baseSize}vw`;
};