export interface ApplicationJson {
    name: string;
    domain: string;
    callback_url: string;
    redirect_url: string;
    description: string;
    schema: Schema;
}

interface NameNode { value: string; }

interface NonNullType {
    kind: 'NonNullType';
    type: Type;
}

interface NamedType {
    kind: 'NamedType';
    name: NameNode;
}

type Type = NonNullType | NamedType;

export interface Schema {
    definitions: SchemaModel[];
}

export interface SchemaModel {
    name: NameNode;
    fields: SchemaModelField[];
}

export interface SchemaModelField {
    name: NameNode;
    type: Type;
    directives: Array<{
        name: NameNode,
    }>;
}

export default class Application {

    public static fromJson(json: ApplicationJson): Application {
        return new Application(json);
    }

    public readonly name: string;
    public readonly domain: string;
    public readonly callbackUrl: string;
    public readonly redirectUrl: string;
    public readonly description: string;
    public readonly schema: Schema;

    constructor(json: ApplicationJson) {
        this.name = json.name;
        this.domain = json.domain;
        this.callbackUrl = json.callback_url;
        this.redirectUrl = json.redirect_url;
        this.description = json.description;
        this.schema = json.schema;
    }

}
