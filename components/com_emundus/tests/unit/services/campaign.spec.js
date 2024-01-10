import campaignService from '../../../src/services/campaign.js';
describe('Service for campaign Http calls',  () => {
    it ('service should exist', () => {
        expect(campaignService).toBeTruthy();
    });

    test('createCampaign, missing params should return false', () => {
        return campaignService.createCampaign({}).then(data => {
            expect(data.status).toBe(false);
        });
    });

    test('pinCampaign, missing params should return false', () => {
        return campaignService.pinCampaign(0).then(data => {
            expect(data.status).toBe(false);
        });
    });
});