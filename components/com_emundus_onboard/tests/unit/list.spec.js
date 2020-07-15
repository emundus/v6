import { expect } from 'chai';
import Vue from 'vue';

import campaign from '../../src/components/list_components/camapaignItem';

describe('campaignItem.vue', () => {
  it('renders data when passed', () => {
    const Constructor = Vue.extend(campaign);
    const comp = new Constructor({
      // Props are passed in "propsData".
      data: {
        published: '1',
        label: 'test',
      },
    }).$mount();
    expect(comp.$data.published).to.equal('1');
    expect(comp.$data.label).to.not.equal('hello');
  });
});
