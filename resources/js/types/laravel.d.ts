type Lang = { [key: string]: string | Lang };

declare const Laravel: LaravelData;

interface Window {
    Laravel: LaravelData;
}

interface LaravelData {
    baseUrl: string,
    lang?: Lang;
    serverSide?: boolean,
    user?: {
        id: string,
        first_name: string,
        last_name: string,
        email: string,
        created_at: number,
        updated_at: number,
    }
}

declare namespace NodeJS {

    interface Global {
        Laravel: LaravelData;
    }

}
