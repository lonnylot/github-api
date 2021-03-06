<?php


namespace MPScholten\GitHubApi\Tests\Api\Repository;


use MPScholten\GitHubApi\Api\Repository\Branch;
use MPScholten\GitHubApi\Api\Repository\Key;
use MPScholten\GitHubApi\Api\Repository\Release;
use MPScholten\GitHubApi\Api\Repository\Repository;
use MPScholten\GitHubApi\Tests\AbstractTestCase;

class RepositoryTest extends AbstractTestCase
{
    public function testPopulateWithExampleData()
    {
        $repository = new Repository();
        $repository->populate($this->loadJsonFixture('fixture_repository.json'));

        $this->assertEquals(1296269, $repository->getId());
        $this->assertEquals('Hello-World', $repository->getName());
        $this->assertFalse($repository->isPrivate());
        $this->assertFalse($repository->isFork());
        $this->assertEquals('This your first repo!', $repository->getDescription());
        $this->assertEquals('git@github.com:octocat/Hello-World.git', $repository->getSshUrl());
        $this->assertEquals('octocat/Hello-World', $repository->getFullName());
        $this->assertEquals('master', $repository->getDefaultBranch());

        $this->assertInstanceOf('MPScholten\GitHubApi\Api\User\User', $repository->getOwner());
        $this->assertEquals('octocat', $repository->getOwner()->getLogin());
    }

    public function testLazyLoadingCommits()
    {
        $httpClient = $this->createHttpClientMock();
        $this->mockSimpleRequest($httpClient, 'get', json_encode($this->loadJsonFixture('fixture2.json')));

        $repository = new Repository($httpClient);
        $repository->populate($this->loadJsonFixture('fixture_repository.json'));

        foreach ($repository->getCommits() as $commit) {
            $this->assertInstanceOf('MPScholten\GitHubApi\Api\Repository\Commit', $commit);
        }
    }

    public function testLazyLoadingCollaborators()
    {
        $httpClient = $this->createHttpClientMock();
        $this->mockSimpleRequest($httpClient, 'get', json_encode($this->loadJsonFixture('fixture3.json')));

        $repository = new Repository($httpClient);
        $repository->populate($this->loadJsonFixture('fixture_repository.json'));

        foreach ($repository->getCollaborators() as $collaborator) {
            $this->assertInstanceOf('MPScholten\GitHubApi\Api\User\User', $collaborator);
        }
    }

    public function testLazyLoadingBranches()
    {
        $httpClient = $this->createHttpClientMock();
        $this->mockSimpleRequest($httpClient, 'get', json_encode($this->loadJsonFixture('fixture_branches.json')));

        $repository = new Repository($httpClient);
        $repository->populate($this->loadJsonFixture('fixture_repository.json'));

        foreach ($repository->getBranches() as $branch) {
            $this->assertInstanceOf(Branch::CLASS_NAME, $branch);
        }
    }

    public function testLazyLoadingKeys()
    {
        $httpClient = $this->createHttpClientMock();
        $this->mockSimpleRequest($httpClient, 'get', json_encode($this->loadJsonFixture('fixture4.json')));

        $repository = new Repository($httpClient);
        $repository->populate($this->loadJsonFixture('fixture_repository.json'));

        foreach ($repository->getKeys() as $key) {
            $this->assertInstanceOf('MPScholten\GitHubApi\Api\Repository\Key', $key);
        }

        $this->assertEquals(
            $repository->getKeys(),
            $repository->getDeployKeys(),
            'getDeployKeys should return the same as getKeys'
        );
    }

    public function testLazyLoadingReleases()
    {
        $httpClient = $this->createHttpClientMock();
        $this->mockSimpleRequest($httpClient, 'get', json_encode($this->loadJsonFixture('fixture_releases.json')));

        $repository = new Repository($httpClient);
        $repository->populate($this->loadJsonFixture('fixture_repository.json'));

        foreach ($repository->getReleases() as $release) {
            $this->assertInstanceOf(Release::CLASS_NAME, $release);
        }
    }

    public function testAddKey()
    {
        $httpClient = $this->createHttpClientMock();
        $this->mockSimpleRequest($httpClient, 'post', json_encode($this->loadJsonFixture('fixture_key.json')));

        $repository = new Repository($httpClient);
        $repository->populate($this->loadJsonFixture('fixture_repository.json'));

        $key = new Key();
        $key->setTitle('hello word');
        $key->setKey('123');

        $this->assertNull($key->getId());
        $repository->addKey($key);
        $this->assertEquals(1, $key->getId());
    }
}
