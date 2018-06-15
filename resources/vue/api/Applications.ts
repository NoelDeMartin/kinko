import Endpoint from '@/api/Endpoint';

import Application from '@/models/Application';

class Applications extends Endpoint {

    public validate(url: string): Promise<Application> {
        return this.get('applications/validate', { url }).then(Application.fromJson);
    }

}

export default new Applications();
