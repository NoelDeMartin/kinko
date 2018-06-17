import Endpoint from '@/api/Endpoint';

import Application, { Schema } from '@/models/Application';

class Applications extends Endpoint {

    public index(): Promise<Application[]> {
        return this.get('applications').then(applications => applications.map(Application.fromJson));
    }

    public parseSchema(url: string): Promise<Schema> {
        return this.get('applications/parse_schema', { url });
    }

}

export default new Applications();
