<?php

namespace Infrastructure\Persistence\InMemory;

use Domain\Model\Post;
use Domain\Model\PostId;
use Domain\Model\CollectionPostRepository as PostRepository;

class InMemoryPostRepository implements PostRepository
{
    private $posts = [];

    public function add(Post $aPost)
    {
        $this->posts[$aPost->id()->id()] = $aPost;
    }

    public function remove(Post $aPost)
    {
        unset($this->posts[$aPost->id()->id()]);
    }

    public function postOfId(PostId $anId)
    {
        if (isset($this->posts[$anId->id()])) {
            return $this->posts[$anId->id()];
        }

        return null;
    }

    public function latestPosts(\DateTime $sinceADate)
    {
        return $this->filterPosts(
            function(Post $post) use ($sinceADate) {
                return $post->createdAt() > $sinceADate;
            }
        );
    }

    private function filterPosts(callable $fn)
    {
        return array_values(array_filter($this->posts, $fn));
    }

    /**
     * @param InMemoryPostSpecification $specification
     *
     * @return Post[]
     */
    public function query($specification)
    {
        return $this->filterPosts(
            function(Post $post) use ($specification) {
                return $specification->specifies($post);
            }
        );
    }

    public function nextIdentity()
    {
        return new PostId();
    }

    public function size()
    {
        return count($this->posts);
    }
}
