document.querySelectorAll('#correctionTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', function () {

        // タブの active 切り替え
        document.querySelectorAll('#correctionTabs .nav-link')
            .forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        // コンテンツ切り替え
        document.querySelectorAll('.tab-content')
            .forEach(content => content.style.display = 'none');

        const target = this.getAttribute('data-target');
        document.querySelector(target).style.display = 'block';
    });
});