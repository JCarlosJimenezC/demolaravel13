<?php

use App\Models\Task;
use App\Models\User;

describe('TaskController', function () {

    it('devuelve todas las tareas', function () {
        Task::factory()->count(3)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    });

    it('crea una tarea correctamente', function () {
        $user = User::factory()->create();

        $response = $this->postJson('/api/tasks', [
            'name' => 'Tarea de prueba',
            'user_id' => $user->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Tarea de prueba',
                'completed' => false,
            ]);

        $this->assertDatabaseHas('tasks', ['name' => 'Tarea de prueba']);
    });

    it('no crea una tarea sin campos requeridos', function () {
        $response = $this->postJson('/api/tasks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'user_id']);
    });

    it('no crea una tarea con user_id inexistente', function () {
        $response = $this->postJson('/api/tasks', [
            'name' => 'Tarea inválida',
            'user_id' => 9999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    });

    it('muestra una tarea por su id', function () {
        $task = Task::factory()->create();

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => $task->name]);
    });

    it('retorna 404 para una tarea inexistente', function () {
        $response = $this->getJson('/api/tasks/9999');

        $response->assertStatus(404);
    });

    it('actualiza una tarea correctamente', function () {
        $task = Task::factory()->create();
        $user = User::factory()->create();

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'name' => 'Tarea actualizada',
            'user_id' => $user->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Tarea actualizada']);

        $this->assertDatabaseHas('tasks', ['name' => 'Tarea actualizada']);
    });

    it('no actualiza una tarea sin campos requeridos', function () {
        $task = Task::factory()->create();

        $response = $this->putJson("/api/tasks/{$task->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'user_id']);
    });

    it('elimina una tarea correctamente', function () {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    });

    it('marca una tarea como completada', function () {
        $task = Task::factory()->create(['completed' => false]);

        $response = $this->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200)
            ->assertJsonFragment(['completed' => true]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'completed' => true,
        ]);
    });

    it('una tarea completada mantiene su estado en base de datos', function () {
        $task = Task::factory()->create(['completed' => false]);

        $this->patchJson("/api/tasks/{$task->id}/complete");

        expect($task->fresh()->completed)->toBeTrue();
    });
});
