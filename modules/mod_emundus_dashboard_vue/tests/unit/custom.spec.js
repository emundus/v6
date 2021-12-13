import { shallowMount } from '@vue/test-utils'
import Custom from '@/components/Custom.vue'

describe('Custom.vue - Render the FAQ Widget', () => {
    const widget = {"id":"7","name":"custom","label":"FAQ","params":null,"size":"2","size_small":"2","type":"article","class":"faq-widget","position":"1","chart_type":null,"article_id":"1040"}
    const wrapper = shallowMount(Custom, {
      propsData: { widget }
    })

    it('should render the component', () => {
        const tableWrapper = wrapper.find('.tchooz-widget');
        expect(tableWrapper.exists()).toBe(true);
    })

    it('should render the component with the correct props', () => {
        expect(wrapper.props().widget.id).toBe('7');
        expect(wrapper.props().widget.name).toBe('custom');
        expect(wrapper.props().widget.class).toBe('faq-widget');
        expect(wrapper.props().widget.article_id).toBe('1040');
    });

    it('should render the article component', () => {
        const widgetWrapper = wrapper.find('.section-sub-menu .faq-widget');
        expect(widgetWrapper.exists()).toBe(true);
    })

    it('should not render the chart component', () => {
        const widgetWrapper = wrapper.find('#chart-container');
        expect(widgetWrapper.exists()).toBe(false);
    })
})

describe('Custom.vue - Render the Files By Status Widget', () => {
    const widget = {"id":"5","name":"custom","label":"Nombre de dossiers par statut","params":null,"size":"10","size_small":"12","type":"chart","class":"","position":"2","chart_type":"column2d","article_id":null}
    const wrapper = shallowMount(Custom, {
        propsData: { widget }
    })

    it('should render the component', () => {
        const tableWrapper = wrapper.find('.tchooz-widget');
        expect(tableWrapper.exists()).toBe(true);
    })

    it('should render the component with the correct props', () => {
        expect(wrapper.props().widget.id).toBe('5');
        expect(wrapper.props().widget.name).toBe('custom');
        expect(wrapper.props().widget.class).toBe('');
        expect(wrapper.props().widget.article_id).toBe(null);
        expect(wrapper.props().widget.chart_type).toBe('column2d');
    });

    it('should render the chart component', () => {
        const widgetWrapper = wrapper.find('#chart-container');
        expect(widgetWrapper.exists()).toBe(true);
    })
})
