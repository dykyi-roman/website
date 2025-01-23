document.addEventListener('DOMContentLoaded', async function() {
    const voteButton = document.getElementById('voteButton');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const totalVotes = parseInt(voteButton.dataset.totalVotes);
    const app = voteButton.dataset.app;

    // Hide button by default
    voteButton.style.display = 'none';

    // Get current language or default to English
    const currentLang = CookieService.get('locale') || 'en';
    const t = await loadTranslations(currentLang);

    // Generate a unique key for localStorage based on the app
    const localStorageKey = `voted_${app}`;

    // Initial progress bar setup
    function updateProgressBar(currentVotes) {
        const percentage = (currentVotes / totalVotes) * 100;
        progressBar.style.width = `${percentage}%`;
        progressBar.setAttribute('aria-valuenow', currentVotes);
        progressText.textContent = `${currentVotes} / ${totalVotes}`;
    }

    // Check if user has already voted
    function checkVoteStatus() {
        // First, check localStorage
        const hasVotedLocally = localStorage.getItem(localStorageKey) === 'true';
        
        if (hasVotedLocally) {
            return;
        }

        // If not voted locally, check with backend
        fetch(`/votes/app/${app}/status`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // If user has not voted yet, show the button
            if (!data.hasVoted) {
                voteButton.style.display = 'block';
            } else {
                // Save to localStorage if voted
                localStorage.setItem(localStorageKey, 'true');
            }
        })
        .catch(error => {
            console.error('Error checking vote status:', error);
        });
    }

    // Check if there are initial votes
    const initialVotes = parseInt(progressBar.getAttribute('aria-valuenow') || 0);
    updateProgressBar(initialVotes);

    // Check vote status on page load
    checkVoteStatus();

    voteButton.addEventListener('click', function() {
        // Disable button immediately to prevent multiple clicks
        voteButton.disabled = true;
        voteButton.classList.add('disabled');

        // Get current votes
        const currentVotes = parseInt(progressBar.getAttribute('aria-valuenow') || 0);
        const newVotes = currentVotes + 1;

        // Update progress bar
        if (newVotes <= totalVotes) {
            updateProgressBar(newVotes);

            // Send POST request
            fetch(`/votes/app/${app}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Save to localStorage
                localStorage.setItem(localStorageKey, 'true');
                
                // Hide button
                voteButton.style.display = 'none';
                
                // Create and insert thank you message
                const thankYouMessage = document.createElement('div');
                thankYouMessage.classList.add('alert', 'alert-success', 'text-center', 'mt-3');
                thankYouMessage.textContent = t.vote_thanks;
                
                // Insert the message where the button was
                voteButton.parentNode.insertBefore(thankYouMessage, voteButton);
            })
            .catch(error => {
                console.error('Error recording vote:', error);
                // Re-enable button in case of error
                voteButton.disabled = false;
                voteButton.classList.remove('disabled');
            });
        }
    });
});