interface LaravelData {
    baseUrl: string,
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

declare const Laravel: LaravelData;

interface Window {
    Laravel: LaravelData;
}

declare namespace NodeJS {

    interface Global {
        Laravel: LaravelData;
    }

}
