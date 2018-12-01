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

const admin_post = document.getElementById('admin_post');

if (admin_post) {
    admin_post.addEventListener('click', e => {
        if (e.target.className === 'btn btn-danger delete-post btn-sm') {
            if (confirm('Are you sure?')) {
                const id = e.target.getAttribute('data-id');

                fetch(`/admin-page-posts/delete/${id}`, {
                    method: 'DELETE'
                }).then(res => window.location.reload());
            }
        }
    });
}


const admin_user = document.getElementById('admin_user');

if (admin_user) {
    admin_user.addEventListener('click', e => {
        if (e.target.className === 'btn btn-danger delete-user btn-sm') {
            if (confirm('Are you sure?')) {
                const id = e.target.getAttribute('data-id');

                fetch(`/admin-page-users/delete/${id}`, {
                    method: 'DELETE'
                }).then(res => window.location.reload());
            }
        }
    });
}


