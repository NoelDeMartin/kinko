import Endpoint from './Endpoint';

import Collection from '@/models/Collection';

class Collections extends Endpoint {

    public index(): Promise<Collection[]> {
        return this.get('collections').then(collections => collections.map(Collection.fromJson));
    }

}

export default new Collections();
