const addIngredientButton = document.getElementById('addIngredient');
const ingredientsContainer = document.getElementById('ingredients');

if (addIngredientButton && ingredientsContainer) {
    addIngredientButton.addEventListener('click', function () {
        const input = document.createElement('input');

        input.type = 'text';
        input.name = 'ingredients[]';
        input.placeholder = 'e.g. 2 cups flour';
        input.maxLength = 255;
        input.required = true;

        ingredientsContainer.appendChild(input);
    });
}

const heartButtons = document.querySelectorAll('.heart-button');

heartButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        const recipeId = button.dataset.recipeId;

        button.disabled = true;

        fetch('toggle_favorite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'recipe_id=' + encodeURIComponent(recipeId)
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Request failed');
            }

            return response.json();
        })
        .then(function (data) {
            if (!data.success) {
                throw new Error(data.message || 'Unable to update favorite');
            }

            if (data.favorited) {
                button.textContent = '♥';
                button.classList.add('active');
            } else {
                button.textContent = '♡';
                button.classList.remove('active');
            }
        })
        .catch(function (error) {
            alert('Unable to update favorite.');
        })
        .finally(function () {
            button.disabled = false;
        });
    });
});