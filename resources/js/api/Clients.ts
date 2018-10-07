import Endpoint from '@/api/Endpoint';

import Client from '@/models/Client';

class Clients extends Endpoint {

    public index(): Promise<Client[]> {
        return this.get('clients').then(clients => clients.map(Client.fromJson));
    }

}

export default new Clients();
