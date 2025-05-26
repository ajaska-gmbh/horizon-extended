<?php
namespace Laravel\Horizon\Tests\Controller;

use Illuminate\Bus\BatchRepository;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\TagRepository;
use Laravel\Horizon\Tests\ControllerTest;
use Mockery;

class BatchesControllerTest extends ControllerTest
{
    public function test_batch_show_returns_jobs()
    {
        $batch = (object) ['id' => 'batch-id', 'failedJobIds' => []];

        $batches = Mockery::mock(BatchRepository::class);
        $batches->shouldReceive('find')->with('batch-id')->andReturn($batch);
        $this->app->instance(BatchRepository::class, $batches);

        $tags = Mockery::mock(TagRepository::class);
        $tags->shouldReceive('jobs')->with('batch:batch-id')->andReturn(['1']);
        $this->app->instance(TagRepository::class, $tags);

        $jobs = Mockery::mock(JobRepository::class);
        $jobs->shouldReceive('getJobs')->with(['1'])->andReturn(collect([(object) [
            'id' => '1',
            'payload' => json_encode(['pushedAt' => '123']),
        ]]));
        $this->app->instance(JobRepository::class, $jobs);

        $response = $this->actingAs(new Fakes\User)
            ->get('/horizon/api/batches/batch-id');

        $response->assertJson([
            'jobs' => [
                ['id' => '1', 'payload' => ['pushedAt' => '123']],
            ],
        ]);
    }
}
