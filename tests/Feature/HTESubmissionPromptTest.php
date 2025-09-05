<?php

namespace Tests\Feature;

use App\Models\HTE;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HTESubmissionPromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_hte_dashboard_shows_submission_prompt_when_not_submitted()
    {
        // Create a user with HTE that has not submitted
        /** @var User $user */
        $user = User::factory()->create(['role' => 'hte']);
        $hte = HTE::factory()->create([
            'user_id' => $user->id,
            'is_submit' => false
        ]);

        // Act as the HTE user
        $this->actingAs($user);

        // Visit the dashboard
        $response = $this->get('/hte/dashboard');

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert that showSubmissionPrompt is true
        $response->assertInertia(fn ($page) => 
            $page->has('showSubmissionPrompt')
                ->where('showSubmissionPrompt', true)
        );
    }

    public function test_hte_dashboard_hides_submission_prompt_when_submitted()
    {
        // Create a user with HTE that has submitted
        /** @var User $user */
        $user = User::factory()->create(['role' => 'hte']);
        $hte = HTE::factory()->create([
            'user_id' => $user->id,
            'is_submit' => true
        ]);

        // Act as the HTE user
        $this->actingAs($user);

        // Visit the dashboard
        $response = $this->get('/hte/dashboard');

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert that showSubmissionPrompt is false
        $response->assertInertia(fn ($page) => 
            $page->has('showSubmissionPrompt')
                ->where('showSubmissionPrompt', false)
        );
    }

    public function test_hte_profile_shows_submission_prompt_when_not_submitted()
    {
        // Create a user with HTE that has not submitted
        /** @var User $user */
        $user = User::factory()->create(['role' => 'hte']);
        $hte = HTE::factory()->create([
            'user_id' => $user->id,
            'is_submit' => false
        ]);

        // Act as the HTE user
        $this->actingAs($user);

        // Visit the profile
        $response = $this->get('/hte/profile');

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert that showSubmissionPrompt is true
        $response->assertInertia(fn ($page) => 
            $page->has('showSubmissionPrompt')
                ->where('showSubmissionPrompt', true)
        );
    }

    public function test_hte_profile_hides_submission_prompt_when_submitted()
    {
        // Create a user with HTE that has submitted
        /** @var User $user */
        $user = User::factory()->create(['role' => 'hte']);
        $hte = HTE::factory()->create([
            'user_id' => $user->id,
            'is_submit' => true
        ]);

        // Act as the HTE user
        $this->actingAs($user);

        // Visit the profile
        $response = $this->get('/hte/profile');

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert that showSubmissionPrompt is false
        $response->assertInertia(fn ($page) => 
            $page->has('showSubmissionPrompt')
                ->where('showSubmissionPrompt', false)
        );
    }
}
