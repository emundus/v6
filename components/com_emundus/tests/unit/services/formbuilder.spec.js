import formService from '../../../src/services/formbuilder.js';

describe('Service for form builder Http calls', () => {
    it('service should exist', () => {
        expect(formService).toBeTruthy();
    });

    test('getDocumentSample, empty vars should return false', () => {
        return formService.getDocumentSample(0, 0).then(data => {
            expect(data.status).toBe(false);
        });
    });

    test('updateDocument, missing params should return false', () => {
        return formService.updateDocument({profile_id: 1, document: {}}).then(data => {
            expect(data.status).toBe(false);
        });
    });

    test('deleteFormModel, missing params should return false', () => {
        return formService.deleteFormModel(0).then(data => {
            expect(data.status).toBe(false);
        });
    });

    test('deleteFormModelFromId, missing params should return false', () => {
        return formService.deleteFormModelFromId([]).then(data => {
            expect(data.status).toBe(false);
        });
    });
});