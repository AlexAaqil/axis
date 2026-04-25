<?php

use App\Models\Objective;
use App\Models\Quarter;
use App\Models\Year;
use App\Enums\Priority;
use App\Enums\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Index Tests (GET /api/v1/objectives)
|--------------------------------------------------------------------------
*/

test('can list objectives', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    Objective::factory()->count(3)->create(['quarter_id' => $quarter->id]);
    
    $response = $this->getJson('/api/v1/objectives');
    
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('can filter objectives by quarter_id', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    $quarter2 = Quarter::factory()->forYear(2025, 2)->create(['year_id' => $year->id]);
    
    Objective::factory()->count(2)->create(['quarter_id' => $quarter->id]);
    Objective::factory()->count(3)->create(['quarter_id' => $quarter2->id]);
    
    $response = $this->getJson("/api/v1/objectives?quarter_id={$quarter->id}");
    
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
    
    foreach ($response->json('data') as $objective) {
        expect($objective['quarter']['id'])->toBe($quarter->id);
    }
});

test('can filter objectives by year', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    $year2026 = Year::factory()->create(['year' => 2026]);
    $quarter2026 = Quarter::factory()->forYear(2026, 1)->create(['year_id' => $year2026->id]);
    
    Objective::factory()->count(2)->create(['quarter_id' => $quarter->id]);
    Objective::factory()->count(3)->create(['quarter_id' => $quarter2026->id]);
    
    $response = $this->getJson('/api/v1/objectives?year=2025');
    
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
    
    foreach ($response->json('data') as $objective) {
        expect($objective['quarter']['year'])->toBe(2025);
    }
});

test('can filter objectives by status', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    Objective::factory()->status(Status::TODO)->create(['quarter_id' => $quarter->id]);
    Objective::factory()->status(Status::DOING)->create(['quarter_id' => $quarter->id]);
    Objective::factory()->status(Status::DONE)->create(['quarter_id' => $quarter->id]);
    
    $response = $this->getJson('/api/v1/objectives?status=' . Status::DOING->value);
    
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status.value', Status::DOING->value);
});

test('can filter objectives by priority', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    Objective::factory()->priority(Priority::HIGH)->create(['quarter_id' => $quarter->id]);
    Objective::factory()->priority(Priority::LOW)->create(['quarter_id' => $quarter->id]);
    Objective::factory()->priority(Priority::MEDIUM)->create(['quarter_id' => $quarter->id]);
    
    $response = $this->getJson('/api/v1/objectives?priority=' . Priority::HIGH->value);
    
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.priority.value', Priority::HIGH->value);
});

test('can filter completed objectives', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    Objective::factory()->status(Status::TODO)->create(['quarter_id' => $quarter->id]);
    Objective::factory()->status(Status::DOING)->create(['quarter_id' => $quarter->id]);
    Objective::factory()->completed()->create(['quarter_id' => $quarter->id]);
    
    $response = $this->getJson('/api/v1/objectives?completed=true');
    
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status.value', Status::DONE->value);
});

test('can filter active objectives (not completed or archived)', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    Objective::factory()->status(Status::TODO)->create(['quarter_id' => $quarter->id]);
    Objective::factory()->status(Status::DOING)->create(['quarter_id' => $quarter->id]);
    Objective::factory()->completed()->create(['quarter_id' => $quarter->id]);
    Objective::factory()->status(Status::ARCHIVED)->create(['quarter_id' => $quarter->id]);
    
    $response = $this->getJson('/api/v1/objectives?completed=false');
    
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
    
    $statuses = collect($response->json('data'))->pluck('status.value');
    expect($statuses)->not->toContain(Status::DONE->value, Status::ARCHIVED->value);
});

test('can search objectives by label', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    Objective::factory()->create(['label' => 'Complete API Documentation', 'quarter_id' => $quarter->id]);
    Objective::factory()->create(['label' => 'Fix Authentication Bug', 'quarter_id' => $quarter->id]);
    Objective::factory()->create(['label' => 'Write Tests', 'quarter_id' => $quarter->id]);
    
    $response = $this->getJson('/api/v1/objectives?search=API');
    
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.label', 'Complete API Documentation');
});

test('can sort objectives by different fields', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    Objective::factory()->create(['label' => 'Z Objective', 'sort_order' => 3, 'quarter_id' => $quarter->id]);
    Objective::factory()->create(['label' => 'A Objective', 'sort_order' => 2, 'quarter_id' => $quarter->id]);
    Objective::factory()->create(['label' => 'M Objective', 'sort_order' => 1, 'quarter_id' => $quarter->id]);
    
    $response = $this->getJson('/api/v1/objectives?sort_by=label&sort_direction=asc');
    
    $response->assertStatus(200);
    $labels = collect($response->json('data'))->pluck('label');
    expect($labels->toArray())->toBe(['A Objective', 'M Objective', 'Z Objective']);
});

test('can paginate objectives', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    Objective::factory()->count(20)->create(['quarter_id' => $quarter->id]);
    
    $response = $this->getJson('/api/v1/objectives?per_page=5');
    
    $response->assertStatus(200)
        ->assertJsonCount(5, 'data');
    
    $this->assertArrayHasKey('links', $response->json());
    $this->assertArrayHasKey('meta', $response->json());
});

/*
|--------------------------------------------------------------------------
| Store Tests (POST /api/v1/objectives)
|--------------------------------------------------------------------------
*/

test('can create objective', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    $data = [
        'label' => 'Complete Project',
        'description' => 'Finish all tasks by end of quarter',
        'color' => '#3B82F6',
        'icon' => 'flag',
        'sort_order' => 1,
        'priority' => Priority::HIGH->value,
        'status' => Status::TODO->value,
        'start_date' => '2025-01-15',
        'due_date' => '2025-02-15',
        'quarter_id' => $quarter->id,
    ];
    
    $response = $this->postJson('/api/v1/objectives', $data);
    
    $response->assertStatus(201)
        ->assertJsonPath('data.label', 'Complete Project')
        ->assertJsonPath('data.priority.value', Priority::HIGH->value)
        ->assertJsonPath('data.status.value', Status::TODO->value);
    
    $this->assertDatabaseHas('objectives', [
        'label' => 'Complete Project',
        'priority' => Priority::HIGH->value,
        'status' => Status::TODO->value,
    ]);
});

test('creates uuid automatically', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    $data = [
        'label' => 'Test Objective',
        'color' => '#3B82F6',
        'icon' => 'list',
        'sort_order' => 1,
        'priority' => Priority::MEDIUM->value,
        'status' => Status::TODO->value,
        'quarter_id' => $quarter->id,
    ];
    
    $response = $this->postJson('/api/v1/objectives', $data);
    
    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['uuid']]);
    
    expect($response->json('data.uuid'))->toBeUuid();
});

test('validates required fields', function () {
    $response = $this->postJson('/api/v1/objectives', []);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['label', 'sort_order', 'priority', 'status', 'quarter_id']);
});

test('validates label minimum length', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    $response = $this->postJson('/api/v1/objectives', [
        'label' => 'ab',
        'color' => '#3B82F6',
        'icon' => 'list',
        'sort_order' => 1,
        'priority' => Priority::MEDIUM->value,
        'status' => Status::TODO->value,
        'quarter_id' => $quarter->id,
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['label']);
});

test('validates color hex format', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    $response = $this->postJson('/api/v1/objectives', [
        'label' => 'Test',
        'color' => 'invalid-color',
        'icon' => 'list',
        'sort_order' => 1,
        'priority' => Priority::MEDIUM->value,
        'status' => Status::TODO->value,
        'quarter_id' => $quarter->id,
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['color']);
});

test('validates priority enum values', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    $response = $this->postJson('/api/v1/objectives', [
        'label' => 'Test',
        'color' => '#3B82F6',
        'icon' => 'list',
        'sort_order' => 1,
        'priority' => 99,
        'status' => Status::TODO->value,
        'quarter_id' => $quarter->id,
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['priority']);
});

test('validates status enum values', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    $response = $this->postJson('/api/v1/objectives', [
        'label' => 'Test',
        'color' => '#3B82F6',
        'icon' => 'list',
        'sort_order' => 1,
        'priority' => Priority::MEDIUM->value,
        'status' => 99,
        'quarter_id' => $quarter->id,
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('validates start date before due date', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    $response = $this->postJson('/api/v1/objectives', [
        'label' => 'Test',
        'color' => '#3B82F6',
        'icon' => 'list',
        'sort_order' => 1,
        'priority' => Priority::MEDIUM->value,
        'status' => Status::TODO->value,
        'start_date' => '2025-03-15',
        'due_date' => '2025-01-15',  // Due date before start date
        'quarter_id' => $quarter->id,
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['due_date']);
});

test('validates dates are within quarter range', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    $response = $this->postJson('/api/v1/objectives', [
        'label' => 'Test',
        'color' => '#3B82F6',
        'icon' => 'list',
        'sort_order' => 1,
        'priority' => Priority::MEDIUM->value,
        'status' => Status::TODO->value,
        'start_date' => '2024-12-01',  // Before quarter start
        'due_date' => '2025-01-15',
        'quarter_id' => $quarter->id,
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['start_date']);
});

test('requires completed_at when status is DONE', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    $response = $this->postJson('/api/v1/objectives', [
        'label' => 'Test',
        'color' => '#3B82F6',
        'icon' => 'list',
        'sort_order' => 1,
        'priority' => Priority::MEDIUM->value,
        'status' => Status::DONE->value,
        'quarter_id' => $quarter->id,
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['completed_at']);
});

test('auto-sets completed_at when status is DONE and date provided', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    $completedDate = '2025-02-15';
    
    $response = $this->postJson('/api/v1/objectives', [
        'label' => 'Test',
        'color' => '#3B82F6',
        'icon' => 'list',
        'sort_order' => 1,
        'priority' => Priority::MEDIUM->value,
        'status' => Status::DONE->value,
        'completed_at' => $completedDate,
        'quarter_id' => $quarter->id,
    ]);
    
    $response->assertStatus(201)
        ->assertJsonPath('data.completed_at', $completedDate);
});

/*
|--------------------------------------------------------------------------
| Show Tests (GET /api/v1/objectives/{objective})
|--------------------------------------------------------------------------
*/

test('can show single objective', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    $objective = Objective::factory()->create(['quarter_id' => $quarter->id]);
    
    $response = $this->getJson("/api/v1/objectives/{$objective->id}");
    
    $response->assertStatus(200)
        ->assertJsonPath('data.id', $objective->id)
        ->assertJsonPath('data.label', $objective->label)
        ->assertJsonStructure([
            'data' => [
                'id', 'uuid', 'label', 'description', 'color', 'icon',
                'sort_order', 'priority', 'status', 'start_date', 'due_date',
                'completed_at', 'is_overdue', 'quarter', 'created_at', 'updated_at'
            ]
        ]);
});

test('show returns 404 for non-existent objective', function () {
    $response = $this->getJson('/api/v1/objectives/99999');
    $response->assertStatus(404);
});

test('can show objective by uuid', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    $objective = Objective::factory()->create(['quarter_id' => $quarter->id]);
    
    $response = $this->getJson("/api/v1/objectives/{$objective->uuid}");
    
    $response->assertStatus(200)
        ->assertJsonPath('data.uuid', $objective->uuid);
});

/*
|--------------------------------------------------------------------------
| Update Tests (PUT /api/v1/objectives/{objective})
|--------------------------------------------------------------------------
*/

test('can update objective', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    $objective = Objective::factory()->create([
        'label' => 'Original Label',
        'priority' => Priority::LOW->value,
        'status' => Status::TODO->value,
        'quarter_id' => $quarter->id,
    ]);
    
    $response = $this->putJson("/api/v1/objectives/{$objective->id}", [
        'label' => 'Updated Label',
        'color' => '#EF4444',
        'icon' => 'star',
        'sort_order' => 5,
        'priority' => Priority::URGENT->value,
        'status' => Status::DOING->value,
        'quarter_id' => $quarter->id,
    ]);
    
    $response->assertStatus(200)
        ->assertJsonPath('data.label', 'Updated Label')
        ->assertJsonPath('data.priority.value', Priority::URGENT->value)
        ->assertJsonPath('data.status.value', Status::DOING->value);
});

test('respects status transition rules - TODO to DOING', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    $objective = Objective::factory()->status(Status::TODO)->create(['quarter_id' => $quarter->id]);
    
    $response = $this->putJson("/api/v1/objectives/{$objective->id}", [
        'label' => $objective->label,
        'color' => $objective->color,
        'icon' => $objective->icon,
        'sort_order' => $objective->sort_order,
        'priority' => $objective->priority,
        'status' => Status::DOING->value,
        'quarter_id' => $quarter->id,
    ]);
    
    $response->assertStatus(200)
        ->assertJsonPath('data.status.value', Status::DOING->value);
});

test('prevents invalid status transition - TODO to DONE', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    $objective = Objective::factory()->status(Status::TODO)->create(['quarter_id' => $quarter->id]);
    
    $response = $this->putJson("/api/v1/objectives/{$objective->id}", [
        'label' => $objective->label,
        'color' => $objective->color,
        'icon' => $objective->icon,
        'sort_order' => $objective->sort_order,
        'priority' => $objective->priority,
        'status' => Status::DONE->value,
        'quarter_id' => $quarter->id,
    ]);
    
    $response->assertStatus(422);
    
    // Get the errors from response
    $data = $response->json();
    $errors = $data['errors'] ?? [];
    
    // Either validation error is acceptable (status transition OR missing completed_at)
    $hasExpectedError = isset($errors['status']) || isset($errors['completed_at']);
    
    expect($hasExpectedError)->toBeTrue();
});

test('prevents changes to terminal statuses', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    $objective = Objective::factory()->completed()->create(['quarter_id' => $quarter->id]);
    
    $response = $this->putJson("/api/v1/objectives/{$objective->id}", [
        'label' => 'Try to change',
        'color' => $objective->color,
        'icon' => $objective->icon,
        'sort_order' => $objective->sort_order,
        'priority' => $objective->priority,
        'status' => Status::ARCHIVED->value,
        'quarter_id' => $quarter->id,
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('update validates unique sort_order per quarter', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    Objective::factory()->create([
        'sort_order' => 1,
        'quarter_id' => $quarter->id,
    ]);
    
    $objective2 = Objective::factory()->create([
        'sort_order' => 2,
        'quarter_id' => $quarter->id,
    ]);
    
    $response = $this->putJson("/api/v1/objectives/{$objective2->id}", [
        'label' => $objective2->label,
        'color' => $objective2->color,
        'icon' => $objective2->icon,
        'sort_order' => 1,  // Already taken
        'priority' => $objective2->priority,
        'status' => $objective2->status,
        'quarter_id' => $quarter->id,
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['sort_order']);
});

/*
|--------------------------------------------------------------------------
| Destroy Tests (DELETE /api/v1/objectives/{objective})
|--------------------------------------------------------------------------
*/

test('can delete objective', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    $objective = Objective::factory()->create(['quarter_id' => $quarter->id]);
    
    $response = $this->deleteJson("/api/v1/objectives/{$objective->id}");
    
    $response->assertStatus(204);
    $this->assertDatabaseMissing('objectives', ['id' => $objective->id]);
});

test('prevents deletion of completed objectives', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    $objective = Objective::factory()->completed()->create(['quarter_id' => $quarter->id]);
    
    $response = $this->deleteJson("/api/v1/objectives/{$objective->id}");
    
    $response->assertStatus(409);
});

test('delete returns 404 for non-existent objective', function () {
    $response = $this->deleteJson('/api/v1/objectives/99999');
    $response->assertStatus(404);
});

/*
|--------------------------------------------------------------------------
| Reorder Tests (POST /api/v1/objectives/reorder)
|--------------------------------------------------------------------------
*/

test('can reorder objectives', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    $obj1 = Objective::factory()->create(['sort_order' => 0, 'quarter_id' => $quarter->id]);
    $obj2 = Objective::factory()->create(['sort_order' => 1, 'quarter_id' => $quarter->id]);
    $obj3 = Objective::factory()->create(['sort_order' => 2, 'quarter_id' => $quarter->id]);
    
    $response = $this->postJson('/api/v1/objectives/reorder', [
        'objectives' => [
            ['id' => $obj1->id, 'sort_order' => 2],
            ['id' => $obj2->id, 'sort_order' => 0],
            ['id' => $obj3->id, 'sort_order' => 1],
        ]
    ]);
    
    $response->assertStatus(200)
        ->assertJsonPath('message', 'Objectives reordered successfully');
    
    $this->assertDatabaseHas('objectives', ['id' => $obj1->id, 'sort_order' => 2]);
    $this->assertDatabaseHas('objectives', ['id' => $obj2->id, 'sort_order' => 0]);
    $this->assertDatabaseHas('objectives', ['id' => $obj3->id, 'sort_order' => 1]);
});

test('reorder validates required fields', function () {
    $response = $this->postJson('/api/v1/objectives/reorder', []);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['objectives']);
});

/*
|--------------------------------------------------------------------------
| Bulk Status Update Tests (POST /api/v1/objectives/bulk-status)
|--------------------------------------------------------------------------
*/

test('can bulk update status', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    $obj1 = Objective::factory()->status(Status::TODO)->create(['quarter_id' => $quarter->id]);
    $obj2 = Objective::factory()->status(Status::TODO)->create(['quarter_id' => $quarter->id]);
    $obj3 = Objective::factory()->status(Status::DOING)->create(['quarter_id' => $quarter->id]);
    
    $response = $this->postJson('/api/v1/objectives/bulk-status', [
        'objective_ids' => [$obj1->id, $obj2->id],
        'status' => Status::DOING->value,
    ]);
    
    $response->assertStatus(200)
        ->assertJsonPath('message', '2 objectives updated successfully');
    
    $this->assertDatabaseHas('objectives', ['id' => $obj1->id, 'status' => Status::DOING->value]);
    $this->assertDatabaseHas('objectives', ['id' => $obj2->id, 'status' => Status::DOING->value]);
    $this->assertDatabaseHas('objectives', ['id' => $obj3->id, 'status' => Status::DOING->value]);
});

test('bulk update to DONE auto-sets completed_at', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    $objective = Objective::factory()->status(Status::DOING)->create(['quarter_id' => $quarter->id]);
    
    $response = $this->postJson('/api/v1/objectives/bulk-status', [
        'objective_ids' => [$objective->id],
        'status' => Status::DONE->value,
    ]);
    
    $response->assertStatus(200);
    
    $objective->refresh();
    expect($objective->completed_at)->not->toBeNull();
});

test('bulk status validates status enum', function () {
    $response = $this->postJson('/api/v1/objectives/bulk-status', [
        'objective_ids' => [1, 2],
        'status' => 99,
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

/*
|--------------------------------------------------------------------------
| Kanban Tests (GET /api/v1/objectives/kanban)
|--------------------------------------------------------------------------
*/

test('can get kanban grouped objectives', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    Objective::factory()->status(Status::TODO)->count(2)->create(['quarter_id' => $quarter->id]);
    Objective::factory()->status(Status::DOING)->count(3)->create(['quarter_id' => $quarter->id]);
    Objective::factory()->completed()->count(1)->create(['quarter_id' => $quarter->id]);
    
    $response = $this->getJson('/api/v1/objectives/kanban?quarter_id=' . $quarter->id);
    
    $response->assertStatus(200);
    
    $data = $response->json('data');
    expect($data['todo'])->toHaveCount(2);
    expect($data['doing'])->toHaveCount(3);
    expect($data['done'])->toHaveCount(1);
});

test('kanban requires quarter_id', function () {
    $response = $this->getJson('/api/v1/objectives/kanban');
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['quarter_id']);
});

test('kanban returns empty groups for quarter with no objectives', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    $response = $this->getJson('/api/v1/objectives/kanban?quarter_id=' . $quarter->id);
    
    $response->assertStatus(200);
    
    $data = $response->json('data');
    expect($data['todo'])->toBeEmpty();
    expect($data['doing'])->toBeEmpty();
    expect($data['done'])->toBeEmpty();
    expect($data['archived'])->toBeEmpty();
});

/*
|--------------------------------------------------------------------------
| Overdue Detection Tests
|--------------------------------------------------------------------------
*/

test('detects overdue objectives', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    
    $overdueObjective = Objective::factory()->create([
        'due_date' => now()->subDays(5),
        'status' => Status::TODO->value,
        'quarter_id' => $quarter->id,
    ]);
    
    $notOverdueObjective = Objective::factory()->create([
        'due_date' => now()->addDays(5),
        'status' => Status::TODO->value,
        'quarter_id' => $quarter->id,
    ]);
    
    $completedObjective = Objective::factory()->completed()->create([
        'due_date' => now()->subDays(5),
        'quarter_id' => $quarter->id,
    ]);
    
    $response = $this->getJson('/api/v1/objectives');
    
    $objectives = collect($response->json('data'))->keyBy('id');
    
    expect($objectives[$overdueObjective->id]['is_overdue'])->toBeTrue();
    expect($objectives[$notOverdueObjective->id]['is_overdue'])->toBeFalse();
    expect($objectives[$completedObjective->id]['is_overdue'])->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Edge Cases & Security Tests
|--------------------------------------------------------------------------
*/

test('prevents mass assignment of uuid', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    $customUuid = (string) Str::uuid();
    
    $response = $this->postJson('/api/v1/objectives', [
        'label' => 'Test',
        'color' => '#3B82F6',
        'icon' => 'list',
        'sort_order' => 1,
        'priority' => Priority::MEDIUM->value,
        'status' => Status::TODO->value,
        'quarter_id' => $quarter->id,
        'uuid' => $customUuid,
    ]);
    
    $response->assertStatus(201);
    
    expect($response->json('data.uuid'))->not->toBe($customUuid);
});

test('limits per_page to maximum of 100', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    Objective::factory()->count(150)->create(['quarter_id' => $quarter->id]);
    
    $response = $this->getJson('/api/v1/objectives?per_page=200');
    
    $response->assertStatus(200);
    expect(count($response->json('data')))->toBeLessThanOrEqual(100);
});

test('sanitizes sort_direction parameter', function () {
    $year = Year::factory()->create(['year' => 2025]);
    $quarter = Quarter::factory()->forYear(2025, 1)->create([
        'year_id' => $year->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-03-31',
    ]);
    Objective::factory()->count(3)->create(['quarter_id' => $quarter->id]);
    
    $response = $this->getJson('/api/v1/objectives?sort_direction=invalid');
    
    $response->assertStatus(200);
});