type RequestMethod = 'GET' | 'POST' | 'PUT' | 'DELETE';

export default class Endpoint {
    protected get(uri: string, data: object = {}): Promise<any> {
        return this.request('GET', uri, data);
    }

    protected async request(
        method: RequestMethod,
        uri: string,
        data: object = {},
        headers: HeadersInit = {},
    ): Promise<any> {
        const url = new URL(Laravel.baseUrl + '/api/' + uri);
        const options: RequestInit = {
            method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                ...headers,
            },
            credentials: 'same-origin',
        };

        switch (method) {
            case 'GET':
                Object.keys(data).forEach(key => url.searchParams.append(key, data[key]));
                break;
            case 'POST':
            case 'DELETE': // fall through
            case 'PUT': // fall through
                options.headers = { 'content-type': 'application/json', ...options.headers };
                options.body = JSON.stringify(data);
                break;
        }

        const response = await fetch(url.toString(), options);
        const isJson = response.headers.get('Content-Type') === 'application/json';

        if (response.status !== 200) {
            const error = await (isJson ? response.json() : response.text());

            throw new Error(error.message ? error.message : error);
        }

        return await (isJson ? response.json() : response.text());
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
