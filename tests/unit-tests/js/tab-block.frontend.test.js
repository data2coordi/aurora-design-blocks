/**
 * @jest-environment jsdom
 */

import '@testing-library/jest-dom';
import { fireEvent } from '@testing-library/dom';

// フロントエンドスクリプト（document.addEventListener('DOMContentLoaded', ...)の中身）を
// 直接関数化してimportまたは貼り付けてもOKですが、ここでは直接実装
function initializeTabsNavigation() {
    if (window.wp && wp.blocks) {
        // ブロックエディター内なら処理しない
        return;
    }

    const tabContainers = document.querySelectorAll('.aurora-design-blocks-tabs');

    tabContainers.forEach(container => {
        const tabs = container.querySelectorAll('.tab');
        if (tabs.length === 0) return;

        const nav = document.createElement('ul');
        nav.className = 'tabs-navigation';

        tabs.forEach((tab, index) => {
            const titleElement = tab.querySelector('.tab-title h4');
            const title = titleElement && titleElement.textContent.trim()
                ? titleElement.textContent.trim()
                : `Tab ${index + 1}`;

            const li = document.createElement('li');
            li.textContent = title;
            li.addEventListener('click', () => {
                tabs.forEach(t => t.style.display = 'none');
                nav.querySelectorAll('li').forEach(item => item.classList.remove('active'));
                tab.style.display = 'block';
                li.classList.add('active');
            });
            nav.appendChild(li);

            if (index === 0) {
                li.classList.add('active');
                tab.style.display = 'block';
            } else {
                tab.style.display = 'none';
            }
        });

        container.insertBefore(nav, container.firstChild);
    });
}

describe('aurora-design-blocks tabs frontend script', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div class="aurora-design-blocks-tabs">
                <div class="tab">
                    <div class="tab-title"><h4>First Tab</h4></div>
                    <div>Content 1</div>
                </div>
                <div class="tab">
                    <div class="tab-title"><h4>Second Tab</h4></div>
                    <div>Content 2</div>
                </div>
                <div class="tab">
                    <div class="tab-title"><h4> </h4></div> <!-- 空白文字 -->
                    <div>Content 3</div>
                </div>
            </div>
        `;

        // window.wp.blocksがある場合は処理スキップなので念のため削除
        delete window.wp;
    });

    test('initializes tabs navigation and shows first tab', () => {
        initializeTabsNavigation();

        // .tabs-navigationが生成されていること
        const nav = document.querySelector('.tabs-navigation');
        expect(nav).toBeInTheDocument();

        // li要素が3つできていること
        const lis = nav.querySelectorAll('li');
        expect(lis.length).toBe(3);

        expect(lis[0]).toHaveTextContent('First Tab');
        expect(lis[0]).toHaveClass('active');

        expect(lis[1]).toHaveTextContent('Second Tab');
        expect(lis[1]).not.toHaveClass('active');

        expect(lis[2]).toHaveTextContent('Tab 3'); // 空白のh4はデフォルトタイトルになる

        // タブの表示状態
        const tabs = document.querySelectorAll('.tab');
        expect(tabs[0]).toBeVisible();
        expect(tabs[1]).not.toBeVisible();
        expect(tabs[2]).not.toBeVisible();
    });

    test('clicking on tab navigations switches visible tab and active class', () => {
        initializeTabsNavigation();

        const nav = document.querySelector('.tabs-navigation');
        const lis = nav.querySelectorAll('li');
        const tabs = document.querySelectorAll('.tab');

        // 最初は1番目がアクティブ
        expect(lis[0]).toHaveClass('active');
        expect(tabs[0]).toBeVisible();

        // 2番目のタブをクリック
        lis[1].click();
        expect(lis[1]).toHaveClass('active');
        expect(lis[0]).not.toHaveClass('active');
        expect(tabs[1]).toBeVisible();
        expect(tabs[0]).not.toBeVisible();

        // 3番目のタブをクリック
        lis[2].click();
        expect(lis[2]).toHaveClass('active');
        expect(lis[1]).not.toHaveClass('active');
        expect(tabs[2]).toBeVisible();
        expect(tabs[1]).not.toBeVisible();
    });

    test('does nothing if window.wp.blocks exists', () => {
        // window.wp.blocksを設定しておく
        window.wp = { blocks: {} };

        initializeTabsNavigation();

        // navは生成されない
        expect(document.querySelector('.tabs-navigation')).not.toBeInTheDocument();
    });
});
