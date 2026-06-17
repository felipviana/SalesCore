function renderHeader() {
    const headerElement = document.getElementById('appHeader');

    if (!headerElement) {
        return;
    }

    const currentPage = document.body.dataset.page || '';

    const links = [
        {
            page: 'home',
            label: 'Início',
            href: '/index.html'
        },
        {
            page: 'products',
            label: 'Produtos',
            href: '/products.html'
        },
        {
            page: 'categories',
            label: 'Categorias',
            href: '/categories.html'
        },
        {
            page: 'suppliers',
            label: 'Fornecedores',
            href: '/suppliers.html'
        },
        {
            page: 'customers',
            label: 'Clientes',
            href: '/customers.html'
        },
        {
            page: 'payment-methods',
            label: 'Formas de Pagamento',
            href: '/payment-methods.html'
        },
        {
            page: 'sales',
            label: 'Vendas',
            href: '/sales.html'
        },
        {
            page: 'sales-report',
            label: 'Relatório de Vendas',
            href: '/sales-report.html'
        },
        {
            page: 'stock-movements',
            label: 'Estoque',
            href: '/stock-movements.html'
        },
        {
            page: 'users',
            label: 'Usuários',
            href: '/users.html'
        }
    ];

    const menuLinks = links.map(link => {
        const activeClass = link.page === currentPage ? 'active fw-bold' : '';

        return `
            <li class="nav-item">
                <a class="nav-link ${activeClass}" href="${link.href}">
                    ${link.label}
                </a>
            </li>
        `;
    }).join('');

    headerElement.innerHTML = `
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand fw-bold" href="/index.html">
                    SalesCore
                </a>

                <button 
                    class="navbar-toggler" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#mainNavbar" 
                    aria-controls="mainNavbar" 
                    aria-expanded="false" 
                    aria-label="Alternar navegação"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav ms-auto me-3">
                        ${menuLinks}
                    </ul>

                    <button class="btn btn-outline-light btn-sm" onclick="logout()">
                        Sair
                    </button>
                </div>
            </div>
        </nav>
    `;
}

document.addEventListener('DOMContentLoaded', renderHeader);