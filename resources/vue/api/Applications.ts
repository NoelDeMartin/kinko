import Endpoint from '@/api/Endpoint';

import { Schema } from '@/models/Application';

class Applications extends Endpoint {

    public parseSchema(url: string): Promise<Schema> {
        return this.get('applications/parse_schema', { url });
    }

}

export default new Applications();
