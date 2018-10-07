export interface CollectionJson {
    name: string;
}

export default class Collection {

    public static fromJson(json: CollectionJson): Collection {
        return new Collection(json);
    }

    public readonly name: string;

    constructor(json: CollectionJson) {
        this.name = json.name;
    }

}
