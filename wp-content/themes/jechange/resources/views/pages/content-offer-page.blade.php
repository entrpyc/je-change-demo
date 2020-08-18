@foreach($data['posts'] as $post)
<pre>
    @dump(get_fields($post->ID))
</pre>

title
description
pictograms
features
provider - name - logo
@endforeach