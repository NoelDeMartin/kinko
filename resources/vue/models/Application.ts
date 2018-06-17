export interface ApplicationJson {
    domain: string;
    callback_url: string;
    redirect_url: string;
    description: string;
    schema: Schema;
}

export interface Schema {
    [field: string]: SchemaField;
}

export interface SchemaField {
    type: string;
    required: boolean;
}

export default class Application {

    public static fromJson(json: ApplicationJson): Application {
        return new Application(json);
    }

    public readonly domain: string;
    public readonly callbackUrl: string;
    public readonly redirectUrl: string;
    public readonly description: string;
    public readonly schema: Schema;

    constructor(json: ApplicationJson) {
        this.domain = json.domain;
        this.callbackUrl = json.callback_url;
        this.redirectUrl = json.redirect_url;
        this.description = json.description;
        this.schema = json.schema;
    }

}
