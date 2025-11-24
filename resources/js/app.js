
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
        this.preloadCriticalPages();
        this.initializePageSpecificScripts(window.location.href);
    }

    setupEventListeners() {
      // --- 1. Lógica de Hover para Menús ---
        const menuItems = document.querySelectorAll('.menu li.has-submenu');
        
        menuItems.forEach(item => {
            const timeoutDelay = 200; // Delay de 200ms antes de cerrar

            item.addEventListener('mouseenter', () => {
                // Limpia cualquier "timeout" de cierre pendiente
                if (this.menuTimeouts.has(item)) {
                    clearTimeout(this.menuTimeouts.get(item));
                    this.menuTimeouts.delete(item);
                }
                
                // Abre este menú
                item.classList.add('open');
                
                // Cierra los hermanos (menús del mismo nivel)
                const siblings = this.getSiblings(item);
                siblings.forEach(sibling => {
                    if (sibling.classList && sibling.classList.contains('has-submenu')) {
                        sibling.classList.remove('open');
                    }
                });
            });

            item.addEventListener('mouseleave', () => {
                // Inicia un "timeout" para cerrar este menú
                const timeoutId = setTimeout(() => {
                    item.classList.remove('open');
                }, timeoutDelay);
                this.menuTimeouts.set(item, timeoutId);
            });
        });
    document.addEventListener('click', (e) => {
        
        if (!(e.target instanceof Element)) {
            return; 
        }

        const link = e.target.closest('.menu a'); 
        const parentLi = link ? link.closest('li') : null;

        // --- Lógica para Abrir/Cerrar Submenús ---
        if (parentLi && parentLi.classList.contains('has-submenu')) {
            e.preventDefault(); // Prevenir navegación si es un submenú
            parentLi.classList.toggle('open'); // Abrir/cerrar el submenú clickeado

            // Cerrar OTROS submenús que pudieran estar abiertos
            document.querySelectorAll('.has-submenu.open').forEach(openSubmenu => {
                if (openSubmenu !== parentLi) {
                    openSubmenu.classList.remove('open');
                }
            });
            return; 
        }
        
        // --- Lógica para CERRAR submenús al hacer clic FUERA ---
        // Si el clic NO fue dentro de un submenú o su botón...
        if (!e.target.closest('.has-submenu')) {
            // Buscamos todos los submenús que estén abiertos y los cerramos
            document.querySelectorAll('.has-submenu.open').forEach(openSubmenu => {
                openSubmenu.classList.remove('open');
            });
        }

        // --- Lógica Original de la SPA (si el clic NO fue en un submenú) ---
        if (link && this.shouldIntercept(link)) {
            e.preventDefault();
            this.setImmediateActivate(link);
            this.navigate(link.href);
        }
    });

        // Manejar botón atrás/adelante del navegador
        window.addEventListener('popstate', (e) => {
            if (!(e.target instanceof Element)) return;
            if (e.state && e.state.page) {
                this.loadPage(e.state.page, false);
            }
        });

        // Precargar al hacer hover (optimizado)
        let hoverTimeout;
        document.addEventListener('mouseenter', (e) => {
            if (!(e.target instanceof Element)) return;
            const link = e.target.closest('.menu a');
            if (link && this.shouldIntercept(link)) {
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(() => {
                    this.preloadPage(link.href);
                }, 200);
            }
        }, true);

        document.addEventListener('mouseleave', (e) => {
            if (!(e.target instanceof Element)) return;
            const link = e.target.closest('.menu a');
            if (link) {
                clearTimeout(hoverTimeout);
            }
        }, true);
    }

    setImmediateActivate(link) {
        
        document.querySelectorAll('.menu li').forEach(li => {
            li.classList.remove('spa-activating');
        });

        const li = link.closest('li');
        if (li) {
            
            document.querySelectorAll('.menu li').forEach(other => {
                if (other !== li) other.classList.remove('active');
            });

            
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

    async loadPage(url, updateHistory = true) {
        // Verificar cache
        const cacheKey = this.getCacheKey(url);
        if (this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < 300000) { // 5 minutos
                if (updateHistory) {
                    history.pushState({ page: url }, '', url);
                }
                return cached.content;
            }
        }

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000);

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': this.getCSRFToken(),
                    'Cache-Control': 'no-cache'
                },
                credentials: 'same-origin',
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const html = await response.text();
            const content = this.parsePageContent(html);
            
            if (!content) {
                throw new Error('Invalid page structure');
            }

            // Cachear la página
            this.cache.set(cacheKey, {
                content: content,
                timestamp: Date.now()
            });

            // Limpiar cache si es muy grande
            if (this.cache.size > 15) {
                this.cleanupCache();
            }

            if (updateHistory) {
                history.pushState({ page: url }, content.title, url);
            }

            return content;
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('Request timeout');
            }
            throw error;
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

                this.initializePageSpecificScripts(url);

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

    initializePageSpecificScripts(url) {
        
        
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
        const criticalPages = ['/cursos', '/mi-informacion', '/ajustes'];
        
        setTimeout(() => {
            criticalPages.forEach(page => {
                if (page !== this.currentPage) {
                    this.preloadPage(window.location.origin + page);
                }
            });
        }, 3000);
    }

    async preloadPage(url) {
        if (!this.cache.has(this.getCacheKey(url)) && !this.isLoading) {
            try {
                await this.loadPage(url, false);
            } catch (error) {
                // Silenciar errores de precarga
            }
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
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.spaNav = new SimpleSPANavigation();
    
    // Exponer método de navegación globalmente
    window.navigateTo = (url) => window.spaNav.navigateTo(url);

    /**
     * Función reutilizable para marcar una actividad como completada (vía AJAX)
     */
        // --- 1. Para LINKS (PDF, Texto, Quiz) ---
        document.body.addEventListener('click', function(event) {
            // 'event.target.closest' es la forma moderna de delegar eventos
            const link = event.target.closest('.auto-complete-link');
            if (link) {
                const activityId = link.dataset.activityId;
                markActivityAsComplete(activityId);
                // La navegación al link (href) ocurre de forma natural
            }
        });

        // --- 2. Para VIDEOS (al finalizar) ---
        const videoPlayers = document.querySelectorAll('.auto-complete-video');
        videoPlayers.forEach(video => {
            video.addEventListener('ended', (event) => {
                const activityId = event.currentTarget.dataset.activityId;
                markActivityAsComplete(activityId);
            });
        });
});

// Limpiar al cerrar
window.addEventListener('beforeunload', () => {
    if (window.spaNav) {
        window.spaNav.clearCache();
    }
});

// --- Manejo permanente del botón context-switcher ---
(function initializeContextSwitcher() {
    // Evita registrar múltiples veces
    if (initializeContextSwitcher._initialized) return;
    initializeContextSwitcher._initialized = true;

    document.addEventListener('click', (event) => {
        const button = event.target.closest('#context-switcher-button');
        const menu = document.getElementById('context-switcher-menu');

        // Si el clic es en el botón, alternar menú
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

    
})();
