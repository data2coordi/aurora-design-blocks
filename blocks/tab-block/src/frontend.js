document.addEventListener('DOMContentLoaded', function () {
    // ブロックエディター内では処理しない
    if (window.wp && wp.blocks) {
        console.log('Block editor detected, skipping frontend script.');
        return;
    }

    const tabContainers = document.querySelectorAll('.aurora-design-blocks-tabs ');

    tabContainers.forEach(container => {
        const tabs = container.querySelectorAll('.tab');
        if (tabs.length === 0) return;

        // タブナビゲーションを動的に作成
        const nav = document.createElement('ul');
        nav.className = 'tabs-navigation';

        tabs.forEach((tab, index) => {
            const titleElement = tab.querySelector('.tab-title h4');
            const title = titleElement ? titleElement.textContent.trim() : `Tab ${index + 1}`;

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
});
