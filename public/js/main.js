const posts = document.getElementById('postdata');

if (posts) {
    posts.addEventListener('click', e => {
        if (e.target.className === 'btn btn-danger delete-post') {
            if (confirm('Are you sure?')) {
                const id = e.target.getAttribute('data-id');

                fetch(`/my-posts/delete/${id}`, {
                    method: 'DELETE'
                }).then(res => window.location.reload());
            }
        }
    });
}



