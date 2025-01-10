document.addEventListener('DOMContentLoaded', function() {
    // Handle review form submission
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: result.message || 'Review submitted successfully!',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: result.message || 'An error occurred while submitting your review.',
                        icon: 'error'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while submitting your review.',
                    icon: 'error'
                });
            }
        });
    }

    // Handle edit review
    const editButtons = document.querySelectorAll('.edit-review');
    editButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const reviewId = this.dataset.reviewId;
            const currentRating = this.dataset.rating;
            const currentReviewText = this.dataset.reviewText;
            
            const { value: formValues } = await Swal.fire({
                title: 'Edit Review',
                html:
                    `<div class="rating-input">
                        <label>Rating:</label>
                        <div class="star-rating">
                            ${Array(5).fill().map((_, i) => `
                                <input type="radio" id="edit_star${i + 1}" name="rating" value="${i + 1}" ${currentRating == i + 1 ? 'checked' : ''}>
                                <label for="edit_star${i + 1}">☆</label>
                            `).join('')}
                        </div>
                    </div>
                    <textarea id="edit_review_text" class="swal2-textarea" placeholder="Your review">${currentReviewText}</textarea>`,
                focusConfirm: false,
                preConfirm: () => {
                    const rating = document.querySelector('input[name="rating"]:checked')?.value;
                    const reviewText = document.getElementById('edit_review_text').value;
                    if (!rating || !reviewText.trim()) {
                        Swal.showValidationMessage('Please fill in all fields');
                        return false;
                    }
                    return { rating, reviewText }
                }
            });

            if (formValues) {
                try {
                    const response = await fetch('reviews/process.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=edit&review_id=${reviewId}&rating=${formValues.rating}&review_text=${encodeURIComponent(formValues.reviewText)}`
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: result.message || 'Review updated successfully!',
                            icon: 'success'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: result.message || 'An error occurred while updating your review.',
                            icon: 'error'
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while updating your review.',
                        icon: 'error'
                    });
                }
            }
        });
    });

    // Handle delete review
    const deleteButtons = document.querySelectorAll('.delete-review');
    deleteButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const reviewId = this.dataset.reviewId;
            
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('reviews/process.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete&review_id=${reviewId}`
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire(
                            'Deleted!',
                            'Your review has been deleted.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            data.message || 'Failed to delete review.',
                            'error'
                        );
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire(
                        'Error!',
                        'An error occurred while deleting the review.',
                        'error'
                    );
                }
            }
        });
    });
});
