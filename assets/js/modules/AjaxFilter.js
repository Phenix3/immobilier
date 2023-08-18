
/**
 * @property {HTMLElement} sorting
 * @property {HTMLElement} content
 * @property {HTMLFormElement} form
 * @property {HTMLElement} pagination
 */
export default class AjaxFilter
{
    /**
     *
     * @param {HTMLElement} element
     */
    constructor(element) {
        if (element === null) {
            return;
        }
        this.sorting = element.querySelector('.js-filter-sorting');
        this.content = element.querySelector('.js-filter-content');
        this.form = element.querySelector('.js-filter-form');
        this.pagination = element.querySelector('.js-filter-pagination');
        this.bindEvents();

        console.log(element);
    }

    bindEvents () {
        this.sorting.addEventListener('click', e => {
            const a = e.target;
            if (a.tagName === 'A') {
                e.preventDefault();
                console.log(a.getAttribute('href'));
                this.loadUrl(a.getAttribute('href'));
            }
        });

        this.form.querySelectorAll('input').forEach(input => {
            input.addEventListener('change', this.loadForm.bind(this));
        })
    }

    loadForm() {
        const data = new FormData(this.form);
        const url = new URL(this.form.getAttribute('action') || window.location.href);
        const params = new URLSearchParams();

        data.forEach((value, key) => {
            params.append(key, value);
        })

        return this.loadUrl(url.pathname + '?' + params.toString());
    }

     loadUrl(url) {
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log(this.content.innerHTML);
            this.content.innerHTML = data.content;
            this.sorting.innerHTML = data.sorting;
            this.pagination.innerHTML = data.pagination;
            history.replaceState({}, '', url);
        })
        .catch(error => console.log(error));        
    }
}