import axios from 'axios';

const SystemPath = Joomla.getOptions('system.paths');
axios.defaults.baseURL = SystemPath.base !== undefined && SystemPath.base !== '' ? SystemPath.base : '/';

export default (headers = {
    'Content-Type': 'application/json',
    'Cache-Control': 'no-cache',
}) =>
    axios.create({
        timeout: 30000,
        headers,
        responseType: 'json',
        withCredentials: true,
    });
