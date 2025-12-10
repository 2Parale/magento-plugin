<?php

namespace TwoPerformant\BusinessLeagueMarketing\Test\Integration;

use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test to verify that redirects triggered by controllers retain the tracking parameters.
 */
class RedirectPersistenceTest extends AbstractController
{
    /**
     * @magentoAppArea frontend
     * @magentoConfigFixture default/twoperformant/params/big_bear_params ["2pau","2ptt"]
     */
    public function testControllerRedirectPreservesParams()
    {
        // 1. Setup the Request
        // We use a standard Magento path that we know redirects.
        // 'customer/account' redirects to 'customer/account/login' if not logged in.
        $this->getRequest()->setMethod('GET');
        $this->getRequest()->setParams([
            '2pau' => 'tracking_value_1',
            '2ptt' => 'tracking_value_2',
            'irrelevant_param' => 'should_be_ignored'
        ]);

        // 2. Dispatch the request (Simulate visiting the page)
        $this->dispatch('customer/account');

        // 3. Assert it was a redirect
        $this->assertRedirect();

        // 4. Inspect the Location Header
        $locationHeader = $this->getResponse()->getHeader('Location');
        $this->assertNotNull($locationHeader, 'Location header was not found.');
        
        $finalUrl = $locationHeader->getFieldValue();

        // 5. Verify the "Wiring" works
        // If the plugin wasn't registered in di.xml, these asserts would fail 
        // because Magento would just redirect to /login without params.
        $this->assertStringContainsString('2pau=tracking_value_1', $finalUrl);
        $this->assertStringContainsString('2ptt=tracking_value_2', $finalUrl);
        
        // Verify we didn't accidentally include everything
        $this->assertStringNotContainsString('irrelevant_param', $finalUrl);
    }
}
