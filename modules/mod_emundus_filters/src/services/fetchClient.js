export class FetchClient {
  constructor(controller) {
    this.baseUrl = 'index.php?option=com_emundus&controller=' + controller;
  }

  async get(task, params) {
    let url = this.baseUrl + '&task=' + task;

    if (params) {
        for (let key in params) {
            url += '&' + key + '=' + params[key];
        }
    }

    return fetch(url).then(response => {
        if (response.ok) {
            return response.json();
        } else {
            throw new Error('An error occurred while fetching the data. ' + response.status + ' ' + response.statusText + '.');
        }
    }).then(data => {
        return data;
    });
  }

  async post(task, data) {
    let url = this.baseUrl + '&task=' + task;

    let formData = new FormData();
    for (let key in data) {
        formData.append(key, data[key]);
    }

    return fetch(url, {
        method: 'POST',
        body: formData
    }).then(response => {
        if (response.ok) {
            return response.json();
        } else {
            throw new Error('An error occurred while fetching the data. ' + response.status + ' ' + response.statusText + '.');
        }
    }).then(data => {
        return data;
    });
  }
}