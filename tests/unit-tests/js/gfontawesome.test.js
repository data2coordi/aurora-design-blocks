/**
 * @jest-environment jsdom
 */
import React from 'react';
import { render, screen, cleanup, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';

// ---- WordPress / グローバル wp モック ----
const registerFormatType = jest.fn();
const insertMock = jest.fn();

beforeAll(() => {
    // グローバル wp を用意（対象コードは global wp を参照）
    global.wp = {
        element: {
            // React のフックをそのまま使う
            ...React,
            Fragment: React.Fragment,
            useState: React.useState,
            useEffect: React.useEffect,
        },
        richText: {
            registerFormatType,
            insert: insertMock,
        },
        blockEditor: {
            // ツールバーボタンはただのボタンとして扱う
            RichTextToolbarButton: ({ onClick, title }) => (
                <button data-testid="richtext-toolbar-button" aria-label={title} onClick={onClick}>
                    {title || 'button'}
                </button>
            ),
        },
        components: {
            // Modal は children をそのまま表示し、閉じるボタンを用意
            Modal: ({ title, onRequestClose, className, children }) => (
                <div role="dialog" aria-label={title} data-testid="mock-modal" className={className}>
                    <button data-testid="modal-close" onClick={onRequestClose}>x</button>
                    {children}
                </div>
            ),
            Button: ({ onClick, children, ...rest }) => (
                <button data-testid="mock-button" onClick={onClick} {...rest}>{children}</button>
            ),
            TextControl: ({ label, value, onChange, placeholder }) => (
                <label>
                    {label}
                    <input
                        data-testid="mock-textcontrol"
                        aria-label={label}
                        placeholder={placeholder}
                        value={value}
                        onChange={(e) => onChange(e.target.value)}
                    />
                </label>
            ),
            Spinner: () => <div data-testid="mock-spinner">Loading...</div>,
        },
    };

    // i18n はそのまま文字列返し
    jest.mock('@wordpress/i18n', () => ({ __: (text) => text }), { virtual: true });

    // fetch をモック
    global.fetch = jest.fn();
});

afterEach(() => {
    cleanup();
    jest.clearAllMocks();
});

afterAll(() => {
    delete global.wp;
    // @ts-ignore
    delete global.fetch;
});

// ---- テスト対象モジュールを読み込み ----
// 実際のファイルパスに置き換えてください
beforeEach(() => {
    // テスト毎に再読み込みして初期化
    jest.isolateModules(() => {
        require('../../../blocks/gfontawesome/src/index.js'); // ←★置き換え
    });
});

describe('FontAwesome format: FontAwesomeSearchButton', () => {
    test('registerFormatType が正しい名前で呼ばれる', () => {
        expect(registerFormatType).toHaveBeenCalledTimes(1);
        const [name, settings] = registerFormatType.mock.calls[0];
        expect(name).toBe('fontawesome/icon');
        expect(typeof settings.edit).toBe('function');
    });

    test('ツールバーからモーダルを開くと fetch が走り、スピナー→アイコン一覧が表示される', async () => {
        // fetch レスポンス（カテゴリごとの配列）
        const mockData = {
            web_app: ['fa-home', 'fa-user'],
            arrows: ['fa-arrow-up'],
        };
        global.fetch.mockResolvedValueOnce({
            json: () => Promise.resolve(mockData),
        });

        const [, settings] = registerFormatType.mock.calls[0];

        const onChange = jest.fn();
        const value = 'Hello ';
        render(settings.edit({ value, onChange }));

        // ツールバーをクリック -> モーダルが開く
        fireEvent.click(screen.getByTestId('richtext-toolbar-button'));
        expect(screen.getByTestId('mock-modal')).toBeInTheDocument();

        // ローディング表示
        expect(screen.getByTestId('mock-spinner')).toBeInTheDocument();

        // フェッチ完了後、カテゴリ見出しが出る
        await waitFor(() => {
            expect(
                screen.getByRole('heading', { name: /web app/i })
            ).toBeInTheDocument();
            expect(
                screen.getByRole('heading', { name: /arrows/i })
            ).toBeInTheDocument();
        });

        // アイコンボタンが並ぶ（例: fa-home）
        expect(screen.getAllByTestId('mock-button').length).toBeGreaterThan(0);
    });


    test('検索語でフィルタされ、ヒットなしの場合は "Icon not found" を表示', async () => {
        const mockData = { web_app: ['fa-home', 'fa-user'] };
        global.fetch.mockResolvedValueOnce({ json: () => Promise.resolve(mockData) });

        const [, settings] = registerFormatType.mock.calls[0];
        const onChange = jest.fn();

        render(settings.edit({ value: 'X', onChange }));
        fireEvent.click(screen.getByTestId('richtext-toolbar-button'));

        // 見出し（web app）を待つ（※ web_app → web app）
        await waitFor(() => {
            expect(
                screen.getByRole('heading', { name: /web app/i })
            ).toBeInTheDocument();
        });

        // "home" でフィルタ → fa-home が残る
        const search = screen.getByLabelText('Search');
        fireEvent.change(search, { target: { value: 'home' } });

        await waitFor(() => {
            const homeIcon = document.querySelector('i.fas.fa-home');
            expect(homeIcon).toBeTruthy();
            const homeBtn = homeIcon.closest('button');
            expect(homeBtn).toBeTruthy();
        });

        // ヒットなしケース
        fireEvent.change(search, { target: { value: 'zzz_no_hit' } });
        await waitFor(() => {
            expect(screen.getAllByText('Icon not found').length).toBeGreaterThan(0);
        });
    });


    test('アイコンをクリックすると shortcode を insert し、onChange が呼ばれ、モーダルが閉じる', async () => {
        const mockData = { web_app: ['fa-home', 'fa-user'] };
        global.fetch.mockResolvedValueOnce({ json: () => Promise.resolve(mockData) });

        const [, settings] = registerFormatType.mock.calls[0];
        const onChange = jest.fn();
        const value = 'Base ';

        // insert は新しい値を返すモック
        insertMock.mockImplementation((currentValue, shortcode) => currentValue + shortcode);

        render(settings.edit({ value, onChange }));

        // モーダルを開く
        fireEvent.click(screen.getByTestId('richtext-toolbar-button'));

        // 見出し（web app）を待つ（※ web_app → web app）
        await waitFor(() => {
            expect(
                screen.getByRole('heading', { name: /web app/i })
            ).toBeInTheDocument();
        });

        // fa-home アイコンの <i> を特定し、親ボタンをクリック
        const homeIcon = document.querySelector('i.fas.fa-home');
        expect(homeIcon).toBeTruthy();
        const homeBtn = homeIcon.closest('button');
        expect(homeBtn).toBeTruthy();
        fireEvent.click(homeBtn);

        // insert が shortcode 付きで呼ばれる
        const shortcode = `[fontawesome icon=fa-home]`;
        expect(insertMock).toHaveBeenCalledTimes(1);
        expect(insertMock).toHaveBeenCalledWith(value, shortcode);

        // onChange は insert の戻り値で呼ばれる
        expect(onChange).toHaveBeenCalledWith(value + shortcode);

        // モーダルが閉じている
        expect(screen.queryByTestId('mock-modal')).not.toBeInTheDocument();
    });


    test('モーダルの onRequestClose（閉じる）で閉じられる', async () => {
        const mockData = { web_app: ['fa-home'] };
        global.fetch.mockResolvedValueOnce({ json: () => Promise.resolve(mockData) });

        const [, settings] = registerFormatType.mock.calls[0];
        render(settings.edit({ value: '', onChange: jest.fn() }));

        fireEvent.click(screen.getByTestId('richtext-toolbar-button'));
        await waitFor(() => {
            expect(screen.getByTestId('mock-modal')).toBeInTheDocument();
        });

        fireEvent.click(screen.getByTestId('modal-close'));
        expect(screen.queryByTestId('mock-modal')).not.toBeInTheDocument();
    });
});
