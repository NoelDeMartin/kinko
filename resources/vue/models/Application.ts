export interface ApplicationJson {
    domain: string;
    callback_url: string;
    description: string;
    schema_url: string;
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
    public readonly description: string;
    public readonly schemaUrl: string;
    public readonly schema: Schema;

    constructor(json: ApplicationJson) {
        this.domain = json.domain;
        this.callbackUrl = json.callback_url;
        this.description = json.description;
        this.schemaUrl = json.schema_url;
        this.schema = json.schema;
    }

}
