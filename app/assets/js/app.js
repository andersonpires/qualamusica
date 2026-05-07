/**
 * app.js - Utilitários Globais
 * 
 * Funções compartilhadas entre todas as páginas
 */

// Configuração global de Toastr
if (typeof toastr !== 'undefined') {
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "showDuration": 300,
        "hideDuration": 1000,
        "timeOut": 5000,
        "extendedTimeOut": 1000,
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
}

/**
 * API Helper - Fetch wrapper para requisições
 */
const APIClient = {
    /**
     * GET Request
     */
    get: async function(endpoint) {
        try {
            const response = await fetch(endpoint);
            return await response.json();
        } catch (error) {
            console.error('GET Error:', error);
            throw error;
        }
    },

    /**
     * POST Request
     */
    post: async function(endpoint, data) {
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('POST Error:', error);
            throw error;
        }
    },

    /**
     * PUT Request
     */
    put: async function(endpoint, data) {
        try {
            const response = await fetch(endpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('PUT Error:', error);
            throw error;
        }
    },

    /**
     * DELETE Request
     */
    delete: async function(endpoint) {
        try {
            const response = await fetch(endpoint, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            return await response.json();
        } catch (error) {
            console.error('DELETE Error:', error);
            throw error;
        }
    }
};

/**
 * URL Helper - Funções de manipulação de URLs
 */
const URLHelper = {
    /**
     * Obtém parâmetro de query string
     */
    getQueryParam: function(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    },

    /**
     * Obtém todos os parâmetros
     */
    getAllQueryParams: function() {
        const urlParams = new URLSearchParams(window.location.search);
        const params = {};
        for (const [key, value] of urlParams) {
            params[key] = value;
        }
        return params;
    },

    /**
     * Navega para URL
     */
    navigate: function(path) {
        window.location.href = path;
    },

    /**
     * Abre URL em nova aba
     */
    openInNewTab: function(url) {
        window.open(url, '_blank');
    }
};

/**
 * Validação Helper
 */
const Validator = {
    /**
     * Valida URL
     */
    isValidURL: function(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    },

    /**
     * Valida se é URL do YouTube
     */
    isValidYouTubeURL: function(url) {
        const patterns = [
            /youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/,
            /youtu\.be\/([a-zA-Z0-9_-]{11})/,
            /youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/,
        ];

        return patterns.some(pattern => pattern.test(url));
    },

    /**
     * Extrai ID do vídeo do YouTube
     */
    extractYouTubeVideoId: function(url) {
        const patterns = [
            /youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/,
            /youtu\.be\/([a-zA-Z0-9_-]{11})/,
            /youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/,
        ];

        for (const pattern of patterns) {
            const match = url.match(pattern);
            if (match) {
                return match[1];
            }
        }

        return null;
    },

    /**
     * Valida campo vazio
     */
    isNotEmpty: function(value) {
        return value && value.trim().length > 0;
    },

    /**
     * Valida email
     */
    isValidEmail: function(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    /**
     * Valida ano
     */
    isValidYear: function(year) {
        const currentYear = new Date().getFullYear();
        return year >= 1800 && year <= currentYear;
    }
};

/**
 * DOM Helper - Manipulação do DOM
 */
const DOMHelper = {
    /**
     * Seleciona elemento
     */
    select: function(selector) {
        return document.querySelector(selector);
    },

    /**
     * Seleciona múltiplos elementos
     */
    selectAll: function(selector) {
        return document.querySelectorAll(selector);
    },

    /**
     * Cria elemento
     */
    createElement: function(tag, attributes = {}) {
        const element = document.createElement(tag);
        for (const [key, value] of Object.entries(attributes)) {
            if (key === 'class') {
                element.className = value;
            } else {
                element.setAttribute(key, value);
            }
        }
        return element;
    },

    /**
     * Remove elemento
     */
    remove: function(selector) {
        const element = this.select(selector);
        if (element) {
            element.remove();
        }
    },

    /**
     * Adiciona classe
     */
    addClass: function(selector, className) {
        const element = this.select(selector);
        if (element) {
            element.classList.add(className);
        }
    },

    /**
     * Remove classe
     */
    removeClass: function(selector, className) {
        const element = this.select(selector);
        if (element) {
            element.classList.remove(className);
        }
    },

    /**
     * Toggle classe
     */
    toggleClass: function(selector, className) {
        const element = this.select(selector);
        if (element) {
            element.classList.toggle(className);
        }
    },

    /**
     * Define conteúdo
     */
    setContent: function(selector, content) {
        const element = this.select(selector);
        if (element) {
            element.innerHTML = content;
        }
    },

    /**
     * Define atributo
     */
    setAttribute: function(selector, attr, value) {
        const element = this.select(selector);
        if (element) {
            element.setAttribute(attr, value);
        }
    }
};

/**
 * Storage Helper - localStorage com namespace
 */
const StorageHelper = {
    namespace: 'qualamusica_',

    /**
     * Salva dado
     */
    set: function(key, value) {
        try {
            localStorage.setItem(
                this.namespace + key,
                typeof value === 'string' ? value : JSON.stringify(value)
            );
        } catch (e) {
            console.error('Storage Error:', e);
        }
    },

    /**
     * Obtém dado
     */
    get: function(key, parseJSON = true) {
        try {
            const value = localStorage.getItem(this.namespace + key);
            if (!value) return null;
            return parseJSON ? JSON.parse(value) : value;
        } catch (e) {
            console.error('Storage Error:', e);
            return null;
        }
    },

    /**
     * Remove dado
     */
    remove: function(key) {
        localStorage.removeItem(this.namespace + key);
    },

    /**
     * Limpa tudo
     */
    clear: function() {
        const keys = Object.keys(localStorage);
        keys.forEach(key => {
            if (key.startsWith(this.namespace)) {
                localStorage.removeItem(key);
            }
        });
    }
};

/**
 * Logger - Logging estruturado
 */
const Logger = {
    /**
     * Log info
     */
    info: function(message, data = null) {
        console.log('%c[INFO]', 'color: #4953ac; font-weight: bold;', message, data || '');
    },

    /**
     * Log warning
     */
    warn: function(message, data = null) {
        console.warn('%c[WARN]', 'color: #fdd400; font-weight: bold;', message, data || '');
    },

    /**
     * Log error
     */
    error: function(message, data = null) {
        console.error('%c[ERROR]', 'color: #b41340; font-weight: bold;', message, data || '');
    },

    /**
     * Log success
     */
    success: function(message, data = null) {
        console.log('%c[SUCCESS]', 'color: #176a21; font-weight: bold;', message, data || '');
    }
};

// Export para uso global
if (typeof window !== 'undefined') {
    window.APIClient = APIClient;
    window.URLHelper = URLHelper;
    window.Validator = Validator;
    window.DOMHelper = DOMHelper;
    window.StorageHelper = StorageHelper;
    window.Logger = Logger;
}

Logger.success('app.js carregado com sucesso');
