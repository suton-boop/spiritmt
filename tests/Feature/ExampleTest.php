<?php

namespace Tests\Feature;

use App\Models\Presentation;
use App\Models\Slide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Home page renders successfully.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('SpiritMeter');
    }

    /**
     * Participant can join presentation with code.
     */
    public function test_participant_can_join_presentation_with_valid_code(): void
    {
        $user = User::factory()->create();
        $presentation = Presentation::create([
            'user_id' => $user->id,
            'title' => 'Test Sesi',
            'access_code' => '99887766',
            'theme' => 'default',
            'voting_status' => 'open',
            'show_results' => true,
        ]);

        $slide = Slide::create([
            'presentation_id' => $presentation->id,
            'order_index' => 1,
            'type' => 'multiple_choice',
            'question' => 'Pertanyaan Kuis?',
            'options' => [
                ['id' => 'o1', 'text' => 'Pilihan A', 'is_correct' => true],
                ['id' => 'o2', 'text' => 'Pilihan B', 'is_correct' => false],
            ],
        ]);
        $presentation->update(['current_slide_id' => $slide->id]);

        $response = $this->post('/join', ['code' => '99887766']);
        $response->assertRedirect('/m/99887766');

        $participantPage = $this->get('/m/99887766');
        $participantPage->assertStatus(200);
        $participantPage->assertSee('Test Sesi');
    }

    /**
     * Participant can submit vote and receive confirmation.
     */
    public function test_participant_can_submit_vote(): void
    {
        $user = User::factory()->create();
        $presentation = Presentation::create([
            'user_id' => $user->id,
            'title' => 'Test Sesi',
            'access_code' => '11223344',
            'theme' => 'default',
            'voting_status' => 'open',
            'show_results' => true,
        ]);

        $slide = Slide::create([
            'presentation_id' => $presentation->id,
            'order_index' => 1,
            'type' => 'multiple_choice',
            'question' => 'Pilihan Makanan?',
            'options' => [
                ['id' => 'pizza', 'text' => 'Pizza', 'color' => '#6366f1'],
                ['id' => 'burger', 'text' => 'Burger', 'color' => '#10b981'],
            ],
        ]);
        $presentation->update(['current_slide_id' => $slide->id]);

        $voteResponse = $this->postJson('/m/11223344/answer', [
            'slide_id' => $slide->id,
            'value' => 'pizza',
            'name' => 'Budi',
        ]);

        $voteResponse->assertStatus(200);
        $voteResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('responses', [
            'presentation_id' => $presentation->id,
            'slide_id' => $slide->id,
        ]);
    }
}
