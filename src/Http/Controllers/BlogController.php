<?php

namespace Rjcodes\Rjcms\Http\Controllers;

use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\View;
use Rjcodes\Rjcms\Models\Category;
use Rjcodes\Rjcms\Models\Post;

class BlogController extends Controller
{
    /**
     * List the published blog posts.
     */
    public function index(): View
    {
        return view('rjcms::blog.index', [
            'posts' => Post::published()->latest()->paginate(10),
        ]);
    }

    /**
     * List the published posts filed under a category.
     */
    public function category(Category $category): View
    {
        return view('rjcms::blog.category', [
            'category' => $category,
            'posts' => $category->posts()->published()->latest()->paginate(10),
        ]);
    }

    /**
     * Render a single published blog post.
     */
    public function show(Post $post): View
    {
        abort_unless($post->isPublished(), 404);

        $post->load('categories');

        return view($this->postTemplate(), ['post' => $post]);
    }

    /**
     * The Blade view used to render every blog post: the site-wide
     * "blog.post_template" setting when it points at an existing view,
     * otherwise the built-in blog.show.
     */
    private function postTemplate(): string
    {
        $template = setting('blog.post_template');

        return $template && ViewFactory::exists($template) ? $template : 'rjcms::blog.show';
    }
}
