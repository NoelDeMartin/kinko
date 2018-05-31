import Collection, { CollectionJson } from '@/models/Collection';

import Endpoint from './Endpoint';

class Collections extends Endpoint {
    public index(): Promise<Collection[]> {
        return this.get('collections').then(collections => Collection.fromArray(collections));
    }
}

export default new Collections();
