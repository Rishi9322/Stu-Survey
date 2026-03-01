<?php

namespace Tests\Functional;

use Tests\Support\FunctionalTester;

class ApiCest
{
    public function testApiHealthCheck(FunctionalTester $I)
    {
        $I->wantTo('Test API health check endpoint');
        $I->sendGET('/health');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['status' => 'healthy']);
    }

    public function testGetFeedbackList(FunctionalTester $I)
    {
        $I->wantTo('Test getting feedback list from API');
        $I->sendGET('/feedback');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.data');
    }

    public function testCreateFeedbackViaApi(FunctionalTester $I)
    {
        $I->wantTo('Create feedback via API');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/feedback', [
            'user_id' => 1,
            'feedback_type' => 'Course Quality',
            'subject' => 'API Test Feedback',
            'message' => 'This feedback was created via API',
            'rating' => 5
        ]);
        
        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);
    }

    public function testGetSpecificFeedback(FunctionalTester $I)
    {
        $I->wantTo('Get specific feedback by ID');
        $I->sendGET('/feedback/1');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.data.id');
    }

    public function testUpdateFeedbackViaApi(FunctionalTester $I)
    {
        $I->wantTo('Update feedback via API');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/feedback/1', [
            'message' => 'Updated feedback message'
        ]);
        
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function testDeleteFeedbackViaApi(FunctionalTester $I)
    {
        $I->wantTo('Delete feedback via API');
        $I->sendDELETE('/feedback/1');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['success' => true]);
    }

    public function testApiRequiresAuthentication(FunctionalTester $I)
    {
        $I->wantTo('Verify protected endpoints require authentication');
        $I->sendGET('/admin/users');
        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson(['error' => 'Unauthorized']);
    }

    public function testApiReturnsProperErrorForNotFound(FunctionalTester $I)
    {
        $I->wantTo('Verify API returns 404 for non-existent resources');
        $I->sendGET('/feedback/99999');
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
    }
}
