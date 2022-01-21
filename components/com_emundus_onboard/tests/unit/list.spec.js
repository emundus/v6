
import {shallowMount} from "@vue/test-utils";
import Vue  from "vue";
import list from "../../src/views/list";

describe('list.vue selectProgram',()=>{
   const wrapper = shallowMount(list,{
        propsData: {
            type: 'campaign'
        }
    });

    it('expect change selectprogram value to display new val', () =>
    {
        const spyValidate = jest.spyOn(list.validatefilter);

        const select = wrapper.find('#pet-select');
        select.trigger('change');

        expect(selectProgram).toBe();
        expect(spyValidate).toBeCalled();

    })
})
