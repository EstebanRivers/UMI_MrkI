import './bootstrap';
// Navegación SPA optimizada y simplificada

class SimpleSPANavigation {
    constructor() {
        this.cache = new Map();
        this.currentPage = window.location.pathname;
        this.isLoading = false;
        this.menuTimeouts = new Map(); // Para manejar los delays del hover
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updateActiveMenuItem();
        this.preloadCriticalPages();
    }

    // !! ========================================================== !!
    // !! FUNCIÓN setupEventListeners REESCRITA PARA HOVER !!
    // !! ========================================================== !!
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

        // --- 2. Lógica de Clics (Solo para Navegación y Clic-Afuera) ---
        document.addEventListener('click', (e) => {
            if (!(e.target instanceof Element)) return;

            // A. Clic FUERA del menú: Cierra todos los menús
            if (!e.target.closest('.menu')) {
                document.querySelectorAll('.menu .has-submenu.open').forEach(openSubmenu => {
                    openSubmenu.classList.remove('open');
                });
            }
            
            // B. Clic DENTRO de un enlace (Navegación SPA)
            const link = e.target.closest('.menu a');
            // Solo navega si el enlace NO es el padre de un submenú (ej. no es "Ajustes")
            const isSubmenuToggle = link && link.parentElement.classList.contains('has-submenu');
            
            if (link && !isSubmenuToggle && this.shouldIntercept(link)) {
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
            this.navigate(window.location.href);
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
    getSiblings(elem) {
        let siblings = [];
        if (!elem.parentNode) return siblings;
        let sibling = elem.parentNode.firstChild;
        while (sibling) {
            if (sibling.nodeType === 1 && sibling !== elem) {
                siblings.push(sibling);
            }
            sibling = sibling.nextSibling;
        }
        return siblings;
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
            this.cache.set(cacheKey, {
                content: content,
                timestamp: Date.now()
            });
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
            mainElement.style.transition = 'opacity 0.2s ease';
            mainElement.style.opacity = '0.7';
            setTimeout(() => {
                mainElement.innerHTML = content.main;
                mainElement.style.opacity = '1';
                this.executeScripts(content.scripts);
                this.scrollToTop();
            }, 100);
        }
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
    if (initializeContextSwitcher._initialized) return;
    initializeContextSwitcher._initialized = true;
    document.addEventListener('click', (event) => {
        const button = event.target.closest('#context-switcher-button');
        const menu = document.getElementById('context-switcher-menu');
        if (button) {
            event.stopPropagation();
            if (menu) {
                menu.classList.toggle('show');
            }
            return;
        }
        if (menu && menu.classList.contains('show')) {
            menu.classList.remove('show');
        }
    });
})();

// Espera a que todo el contenido del DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Seleccionar los elementos clave por ID
    const menu = document.getElementById('popupRegister');
    const abrirBtn = document.getElementById('openCreateCarrer');
    const cerrarBtn = document.getElementById('cerrarRegistroBtn');
    
    // Si alguno de los elementos no existe, sal de la función para evitar errores
    if (!menu || !abrirBtn || !cerrarBtn) {
        return; 
    }

    // 2. Función para abrir el menú
    function abrirMenu() {
        menu.classList.remove('hidden'); // Remueve la clase 'hidden' para mostrarlo
    }

    // 3. Función para cerrar el menú
    function cerrarMenu() {
        menu.classList.add('hidden'); // Agrega la clase 'hidden' para ocultarlo
    }
    
    // 4. Asignar los eventos a los botones
    abrirBtn.addEventListener('click', abrirMenu);
    cerrarBtn.addEventListener('click', cerrarMenu);

    // 5. Opcional: Cerrar al presionar la tecla ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !menu.classList.contains('hidden')) {
            cerrarMenu();
        }
    });

    // 6. Opcional: Cerrar al hacer click en el fondo (fuera del panel)
    menu.addEventListener('click', function(event) {
        // Solo cerrar si el clic es directamente en el fondo del div del menú,
        // no en un hijo del menú (como el formulario o el botón de registro)
        if (event.target === menu) {
            cerrarMenu();
        }
    });
});