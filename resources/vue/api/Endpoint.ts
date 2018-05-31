export default class Endpoint {
    protected get(uri: string, data: object = {}): Promise<any> {
        return this.request('GET', uri, data);
    }

    protected request(method: 'GET', uri: string, data: object): Promise<any> {
        return fetch('/api/' + uri, {
            method,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.getCsrfToken(),
            },
        }).then(res => res.json());
    }

    private getCsrfToken(): string {
        const token = <HTMLMetaElement> document.head.querySelector('meta[name="csrf-token"]');

        if (token) {
            return token.content;
        } else {
            console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
            return '';
        }
    }
}
