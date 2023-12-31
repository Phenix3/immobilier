import {slideUp} from '../modules/animation';

export class Alert extends HTMLElement {
    constructor({type, message} = {}) {
        super();
        if(type !== undefined) {
            this.type = type;
        } else {
            this.type = this.getAttribute('type');
        }
        if(!message) {
            message = this.innerHTML;
        }
        if(this.type = 'error' || this.type === null) {
            this.type = 'danger';
        }
        this.message = message;
    }

    connectedCallback() {
        this.innerHTML = `<div class="alert alert-${this.type}">
        <svg class="icon icon-{$name}">
          <use xlink:href="/sprite.svg#${this.icon}"></use>
        </svg>
        ${this.message}
        <button class="alert-close">
          <svg class="icon">
            <use xlink:href="/sprite.svg#cross"></use>
          </svg>
        </button>
      </div>`;
        this.querySelector('.alert-close').addEventListener('click', e => {
            e.preventDefault();
            this.close();
        });
    }

    get icon() {
        if(this.type === 'danger') {
            return 'cross';
        } else if (this.type === 'warning') {
            return 'warning';
        } else {
            return 'check';
        }
    }

    close() {
        const el = this.querySelector('.alert');
        el.classList.add('slideOut');
        window.setTimeout(() => {
            slideUp(el);
            this.parentElement.removeChild(this)
        }, 500)
    }
}

export class FloatingAlert extends Alert {
    constructor (options = {}) {
        super(options);
        this.classList.add('is-floating');
        this.style.position = 'fixed';
        this.style.top = '20px';
        this.style.right = '20px';
        this.style.maxWidth = '400px';
        this.style.zIndex = '100';
    }
}

customElements.define('alert-message', Alert);
customElements.define('alert-floating', FloatingAlert);
