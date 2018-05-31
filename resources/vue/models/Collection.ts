export interface CollectionJson {
    name: string;
}

export default class Collection {

    public static fromArray(jsonArray: CollectionJson[]): Collection[] {
        return jsonArray.map(json => new Collection(json));
    }

    public readonly name: string;

    constructor(json: CollectionJson) {
        this.name = json.name;
    }

}
