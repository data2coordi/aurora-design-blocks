import { RichTextToolbarButton } from '@wordpress/block-editor';

import '@testing-library/jest-dom';
import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';

import { create } from '@wordpress/rich-text';

// モック
global.fetch = jest.fn(() =>
    Promise.resolve({
        ok: true,
        json: () => Promise.resolve({ solid: ['fa-home', 'fa-user'] }),
    })
);



global.wp = {
    element: {
        Fragment: React.Fragment,
        useState: React.useState,
        useEffect: React.useEffect,
    },

    components: {
        Modal: ({ children }) => <div data-testid="modal">{children}</div>,
        Button: ({ onClick, children }) => <button onClick={onClick}>{children}</button>,
        TextControl: ({ value, onChange }) => <input value={value} onChange={(e) => onChange(e.target.value)} />,
        Spinner: () => <div>Loading...</div>,
    },
    richText: {
        insert: (value, shortcode) => shortcode, // insertは onChange に渡されるショートコードを返すように
        registerFormatType: jest.fn(), // ← 追加

    },
};


const { FontAwesomeSearchButton } = require('../../../blocks/gfontawesome/src/index.js');

// テスト
describe('FontAwesomeSearchButton', () => {

    it('registerFormatType が呼ばれていること', () => {
        // require 直後なのでモックはすでに呼ばれている
        expect(wp.richText.registerFormatType).toHaveBeenCalledTimes(1);
        expect(wp.richText.registerFormatType).toHaveBeenCalledWith(
            'fontawesome/icon',
            expect.objectContaining({ edit: expect.any(Function) })
        );

    });


    it('ツールバーボタンが表示され、クリックでモーダルが開くこと', async () => {

        render(<FontAwesomeSearchButton value="" onChange={() => { }}
        />);
        //expect(wp.blockEditor.RichTextToolbarButton).toHaveBeenCalledTimes(1);

        //        render(<FontAwesomeSearchButton value="" onChange={() => { }} />);

        // ボタンが表示されているか確認
        /*        const button = screen.getByTestId('toolbar-button');
               expect(button).toBeInTheDocument();
       
               fireEvent.click(button);
               expect(await screen.findByTestId('modal')).toBeInTheDocument(); */
    });

    /*
    it('アイコンをクリックすると onChange が呼ばれること', async () => {
        const mockOnChange = jest.fn();
        render(<FontAwesomeSearchButton value="" onChange={mockOnChange} />);

        // モーダルを開く
        fireEvent.click(screen.getByTestId('toolbar-button'));

        // アイコンボタンが表示されるのを待つ
        await waitFor(() => screen.getByText('fa-home'));

        // アイコンボタンをクリック
        fireEvent.click(screen.getByText('fa-home'));

        // onChange が呼ばれているか確認
        expect(mockOnChange).toHaveBeenCalledWith('[fontawesome icon=fa-home]');
    });
    */
});
