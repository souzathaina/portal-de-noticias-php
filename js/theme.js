document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;

    // Função para aplicar o tema
    function applyTheme(theme) {
        if (theme === 'dark') {
            body.classList.add('dark-mode');
            // Você pode querer mudar a imagem da logo para uma versão mais clara no dark mode
            // Exemplo:
            // const logo = document.querySelector('.logo');
            // if (logo) {
            //     logo.src = 'imagens/logo/logo-dark.png'; // Crie uma logo 'logo-dark.png'
            // }
        } else {
            body.classList.remove('dark-mode');
            // Exemplo:
            // const logo = document.querySelector('.logo');
            // if (logo) {
            //     logo.src = 'imagens/logo/logo.png';
            // }
        }
    }

    // 1. Carregar a preferência do usuário (do Local Storage)
    const savedTheme = localStorage.getItem('theme');

    // 2. Se houver uma preferência salva, aplicá-la
    if (savedTheme) {
        applyTheme(savedTheme);
    } else {
        // 3. Se não houver preferência salva, verificar a preferência do sistema operacional
        //    (media query prefers-color-scheme)
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            applyTheme('dark');
        } else {
            applyTheme('light'); // Padrão se não houver preferência ou for light
        }
    }

    // 4. Adicionar evento de clique ao botão de alternância
    if (themeToggle) { // Garante que o botão existe
        themeToggle.addEventListener('click', () => {
            if (body.classList.contains('dark-mode')) {
                applyTheme('light');
                localStorage.setItem('theme', 'light');
            } else {
                applyTheme('dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    }

    // 5. Opcional: Reagir a mudanças na preferência do sistema operacional (em tempo real)
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
        // Se o usuário não tiver uma preferência salva, siga a preferência do sistema
        if (!localStorage.getItem('theme')) {
            applyTheme(event.matches ? 'dark' : 'light');
        }
    });
});