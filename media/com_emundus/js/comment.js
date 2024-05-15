function openCommentAside(focusonelement = null, forceOpen = false) {
    const aside = document.getElementById('aside-comment-section');
    if (aside.classList.contains('closed') || forceOpen) {
        aside.classList.remove('closed');
    } else {
        aside.classList.add('closed');
    }

    const event = new CustomEvent('focusOnCommentElement', {
        detail: {
            targetId: focusonelement
        }
    });
    document.dispatchEvent(event);
}

function openModalAddComment(element)
{
    const event = new CustomEvent('openModalAddComment', {
        detail: {
            targetType: element.dataset.targetType,
            targetId: element.dataset.targetId,
        }
    });

    document.dispatchEvent(event);
}

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('comment-icon')) {
        if (e.target.classList.contains('has-comments')) {
            openCommentAside(e.target.dataset.targetId, true);
        } else {
            openModalAddComment(e.target);
        }
    }
});

document.addEventListener('commentsLoaded', (e) => {
    if (e.detail.comments.length > 0) {
        e.detail.comments.forEach((comment) => {
            const commentIcon = document.querySelector(`.comment-icon[data-target-id="${comment.target_id}"]`);
            if (commentIcon) {
                commentIcon.classList.add('has-comments');
                commentIcon.classList.add('em-bg-main-500');
                commentIcon.classList.add('em-text-neutral-300');
                commentIcon.classList.add('p-1');
                commentIcon.classList.add('rounded-full');
            }
        });
    }
});