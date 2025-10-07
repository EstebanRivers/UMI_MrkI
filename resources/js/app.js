
function initializeContextSwitcher() {
    // Evita instalar más de una vez
    if (initializeContextSwitcher._initialized) return;
    initializeContextSwitcher._initialized = true;

    document.addEventListener('click', (event) => {
        const button = event.target.closest('#context-switcher-button');
        const menu = document.getElementById('context-switcher-menu');

        // Si se hace clic en el botón, alternar visibilidad
        if (button) {
            event.stopPropagation();
            if (menu) {
                menu.classList.toggle('show');
            }
            return;
        }

        // Si se hace clic fuera, cerrar el menú
        if (menu && menu.classList.contains('show')) {
            menu.classList.remove('show');
        }
    });
}


// Navegación SPA optimizada y simplificada
class SimpleSPANavigation {
    constructor() {
        this.cache = new Map();
        this.currentPage = window.location.pathname;
        this.isLoading = false;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updateActiveMenuItem();
        this.preloadIdlePages();
    }

    setupEventListeners() {
        // --- MEJORA 2: Pre-carga al pasar el mouse (Preloading) ---
        let hoverTimeout;
        document.addEventListener('mouseenter', (e) => {
            const link = e.target.closest('a[href^="/"]'); // Solo enlaces internos
            if (link && this.shouldIntercept(link)) {
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(() => {
                    this.preloadPage(link.href);
                }, 80); // Un pequeño retraso para no ser demasiado agresivo
            }
        }, true); // Usar 'true' para capturar el evento en la fase de captura

        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && this.shouldIntercept(link)) {
                e.preventDefault();
                this.navigate(link.href);
            }
        });

        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.page) {
                this.loadPage(e.state.page, false);
            }
        });
    }

    setImmediateActivate(link) {
        // Remover clase temporal de todos los elementos
        document.querySelectorAll('.menu li').forEach(li => {
            li.classList.remove('spa-activating');
        });

        const li = link.closest('li');
        if (li) {
            // Desactivar otros elementos
            document.querySelectorAll('.menu li').forEach(other => {
                if (other !== li) other.classList.remove('active');
            });

            // Activar el elemento clickeado inmediatamente
            li.classList.add('active', 'spa-activating');
        }
    }

    shouldIntercept(link) {
        return link.hostname === window.location.hostname && 
               !link.hasAttribute('data-no-intercept') &&
               !link.href.includes('logout') &&
               !link.href.includes('#') &&
               !link.closest('.brand'); 
    }

    async navigate(url) {
        if (this.isLoading || url === window.location.href) return;
        
        this.isLoading = true;
        
        try {
            const content = await this.loadPage(url, true);
            if (content) {
                this.updatePage(content, url);
                this.updateActiveMenuItem();
            }
        } catch (error) {
            console.error('Navigation error:', error);
            this.handleNavigationError(error, url);
        } finally {
            this.isLoading = false;
        }
    }

   // Dentro de tu clase SimpleSPANavigation

    async loadPage(url, pushState = true) {
        this.isLoading = true;
        const cacheKey = this.getCacheKey(url);

        try {
            let content;

            // 1️⃣ Usa caché si está disponible
            if (this.cache.has(cacheKey)) {
                const cached = this.cache.get(cacheKey);
                if (Date.now() - cached.timestamp < 300000) {
                    content = cached;
                } else {
                    this.cache.delete(cacheKey);
                }
            }

            // 2️⃣ Si no está en caché, cargar del servidor
            if (!content) {
                const controller = new AbortController();
                const timeout = setTimeout(() => controller.abort(), 8000);

                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    signal: controller.signal
                });

                clearTimeout(timeout);

                if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);

                const html = await response.text();
                content = this.parsePageContent(html);
                this.cache.set(cacheKey, { ...content, timestamp: Date.now() });
            }

            // 3️⃣ Transición + render
            this.updatePage(content, url);

            if (pushState) {
                history.pushState({ page: url }, content.title, url);
            }

            this.updateActiveMenuItem();
            initializeContextSwitcher();

        } catch (error) {
            console.error('Error al navegar:', error);
            if (!(error.name === 'AbortError')) {
                window.location.href = url;
            }
        } finally {
            this.isLoading = false;
        }
    }





    parsePageContent(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        const mainContent = doc.querySelector('#main-content, .main-content');
        const title = doc.querySelector('title')?.textContent || '';
        
        if (!mainContent) {
            return null;
        }

        return {
            main: mainContent.innerHTML,
            title: title,
            scripts: this.extractScripts(mainContent)
        };
    }

    extractScripts(container) {
        const scripts = [];
        const scriptElements = container.querySelectorAll('script');
        
        scriptElements.forEach(script => {
            if (script.src) {
                scripts.push({ type: 'external', src: script.src });
            } else if (script.textContent.trim()) {
                scripts.push({ type: 'inline', content: script.textContent });
            }
        });
        
        return scripts;
    }

    updatePage(content, url) {
        const mainElement = document.querySelector('#main-content, .main-content');
        if (mainElement) {
            // Transición suave sin indicadores de carga
            mainElement.style.transition = 'opacity 0.2s ease';
            mainElement.style.opacity = '0.7';
            
            setTimeout(() => {
                mainElement.innerHTML = content.main;
                mainElement.style.opacity = '1';
                
                // Ejecutar scripts si los hay
                this.executeScripts(content.scripts);
                
                // Scroll suave al top
                this.scrollToTop();
            }, 100);
        }

        // Actualizar título
        document.title = content.title;
        this.currentPage = url;
    }

    executeScripts(scripts) {
        scripts.forEach(script => {
            if (script.type === 'external') {
                if (!document.querySelector(`script[src="${script.src}"]`)) {
                    const newScript = document.createElement('script');
                    newScript.src = script.src;
                    newScript.async = true;
                    document.head.appendChild(newScript);
                }
            } else {
                try {
                    new Function(script.content)();
                } catch (e) {
                    console.warn('Script execution failed:', e);
                }
            }
        });
    }

    updateActiveMenuItem() {
        const currentPath = new URL(window.location.href).pathname.replace(/\/+$/, '') || '/';

        document.querySelectorAll('.menu a').forEach(link => {
            const li = link.closest('li');
            if (!li) return;

            const href = link.getAttribute('href') || '#';
            let linkPath = '/';
            try {
                linkPath = new URL(href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
            } catch (e) {
                linkPath = href.replace(/\/+$/, '') || '/';
            }

            const isMatch = (linkPath === currentPath) || 
                           (linkPath !== '/' && currentPath.startsWith(linkPath + '/')) || 
                           (linkPath !== '/' && currentPath === linkPath);

            if (isMatch) {
                if (!li.classList.contains('active')) {
                    li.classList.add('active');
                }
                li.classList.remove('spa-activating');
            } else {
                if (!li.classList.contains('spa-activating')) {
                    li.classList.remove('active');
                }
            }
        });
    }

    preloadCriticalPages() {
        const criticalPages = ['/dashboard', '/mi-informacion', '/ajustes'];
        
        setTimeout(() => {
            criticalPages.forEach(page => {
                if (page !== this.currentPage) {
                    this.preloadPage(window.location.origin + page);
                }
            });
        }, 3000);
    }

    async preloadPage(url) {
        const cacheKey = this.getCacheKey(url);
        if (this.cache.has(cacheKey) || this.isLoading) return;

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                    'Cache-Control': 'no-cache'
                }
            });

            if (!response.ok) return;

            const html = await response.text();
            const parsed = this.parsePageContent(html);
            if (!parsed) return;

            // Guarda la versión parseada, no solo el HTML crudo
            this.cache.set(cacheKey, { ...parsed, timestamp: Date.now() });

            // Mantén el caché pequeño
            if (this.cache.size > 20) this.cleanupCache();

        } catch (error) {
            console.warn('Precarga fallida:', error);
        }
    }



    handleNavigationError(error, url) {
        console.error('Navigation failed:', error);
        // Fallback: navegar normalmente
        setTimeout(() => {
            window.location.href = url;
        }, 1000);
    }

    scrollToTop() {
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            mainContent.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    getCacheKey(url) {
        return new URL(url).pathname;
    }

    getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    cleanupCache() {
        const entries = Array.from(this.cache.entries())
            .sort((a, b) => b[1].timestamp - a[1].timestamp)
            .slice(0, 10);
        
        this.cache.clear();
        entries.forEach(([key, value]) => {
            this.cache.set(key, value);
        });
    }

    // Métodos públicos
    clearCache() {
        this.cache.clear();
    }

    navigateTo(url) {
        this.navigate(url);
    }

    preloadIdlePages() {
        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => this.preloadCriticalPages());
        } else {
            setTimeout(() => this.preloadCriticalPages(), 3000);
        }
    }

}

// --- Inicialización Principal ---
document.addEventListener('DOMContentLoaded', () => {
    window.spaNav = new SimpleSPANavigation();
    window.navigateTo = (url) => window.spaNav.navigateTo(url);

    // Ejecutamos la función del botón una vez en la carga inicial
    initializeContextSwitcher();
});

// Limpiar al cerrar
window.addEventListener('beforeunload', () => {
    if (window.spaNav) {
        window.spaNav.clearCache();
    }
});
