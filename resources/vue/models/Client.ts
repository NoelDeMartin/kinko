export interface ClientJson {
    id: string;
    name: string;
    description: string;
    logo_url?: string;
    homepage_url: string;
    schema: Schema;
    validated: boolean;
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

export default class Client {

    public static fromJson(json: ClientJson): Client {
        return new Client(json);
    }

    public readonly id: string;
    public readonly name: string;
    public readonly description: string;
    public readonly logoUrl?: string;
    public readonly homepageUrl: string;
    public readonly schema: Schema;
    public readonly validated: boolean;

    constructor(json: ClientJson) {
        this.id = json.id;
        this.name = json.name;
        this.description = json.description;
        this.logoUrl = json.logo_url;
        this.homepageUrl = json.homepage_url;
        this.schema = json.schema;
        this.validated = json.validated;
    }

}
