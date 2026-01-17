<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Proposals</title>

    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #1f1d29; 
            color: #e6e6e6;
        }

        header {
            background: #1abc9c; 
            color: white;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        header h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 0.5px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .back-btn, .logout {
            text-decoration: none;
            color: white;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.6);
            padding: 6px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .back-btn:hover, .logout:hover {
            background: #2e1b36;
            box-shadow: 0 0 15px rgba(26,188,156,0.7), 0 0 25px rgba(155,89,182,0.5);
            transform: scale(1.08);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #1abc9c;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c2a38;
            padding-bottom: 10px;
        }

        .proposals-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .proposal-card {
            background: #2c2a38;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.4);
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .proposal-card:hover {
            transform: translateX(8px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.6);
            border-left-color: #1abc9c;
        }

        .proposal-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }

        .proposal-title {
            font-size: 18px;
            font-weight: 600;
            color: #e6e6e6;
            margin-bottom: 8px;
        }

        .proposal-id {
            font-size: 13px;
            color: #888;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #f39c12;
            color: #1f1d29;
        }

        .status-in-review {
            background: #3498db;
            color: white;
        }

        .proposal-info {
            display: flex;
            gap: 30px;
            margin-bottom: 16px;
            font-size: 14px;
            color: #bbb;
        }

        .proposal-info span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .proposal-description {
            color: #cfcfcf;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .proposal-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #1abc9c;
            color: white;
        }

        .btn-primary:hover {
            background: #16a085;
            box-shadow: 0 4px 12px rgba(26,188,156,0.4);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #34495e;
            color: white;
        }

        .btn-secondary:hover {
            background: #2c3e50;
            transform: translateY(-2px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #2c2a38;
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.8);
        }

        .modal-header {
            font-size: 20px;
            font-weight: 600;
            color: #1abc9c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1f1d29;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #cfcfcf;
            font-size: 14px;
            font-weight: 600;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            background: #1f1d29;
            border: 1px solid #444;
            border-radius: 6px;
            color: #e6e6e6;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
        }

        .form-group input[type="number"] {
            width: 100%;
            padding: 12px;
            background: #1f1d29;
            border: 1px solid #444;
            border-radius: 6px;
            color: #e6e6e6;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 14px;
        }

        .form-group input[type="file"] {
            width: 100%;
            padding: 12px;
            background: #1f1d29;
            border: 1px solid #444;
            border-radius: 6px;
            color: #e6e6e6;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 14px;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
        }

        .score-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .score-input input {
            flex: 0 0 100px;
        }

        .score-label {
            color: #888;
            font-size: 13px;
        }

        footer {
            background: #161421;
            color: #bbb;
            text-align: center;
            padding: 14px;
            font-size: 14px;
            border-top: 1px solid #333333;
            margin-top: 60px;
        }

        footer span {
            font-weight: 600;
        }
    </style>
</head>

<body>

<header>
    <h1>Review Proposals</h1>

    <div class="nav-right">
        <a href="reviewer_main.html" class="back-btn">← Back</a>
        <a href="login.html" class="logout">Logout</a>
    </div>
</header>

<div class="container">
    <div class="section-title">Proposals Awaiting Review</div>

    <div class="proposals-list" id="proposalsList">
        <!-- Proposals will be loaded here -->
    </div>
</div>

<!-- Review Modal -->
<div class="modal" id="reviewModal">
    <div class="modal-content">
        <div class="modal-header" id="modalTitle">Review Proposal</div>
        
        <div class="form-group">
            <label>Review Comments</label>
            <textarea id="reviewComments" placeholder="Provide detailed feedback on the proposal..."></textarea>
        </div>

        <div class="form-group">
            <label>Upload Review Notes (Optional)</label>
            <input type="file" id="reviewFile" accept=".pdf,.doc,.docx,.txt">
        </div>

        <div class="form-group">
            <label>Score</label>
            <div class="score-input">
                <input type="number" id="reviewScore" min="0" max="10" step="0.1" placeholder="0.0">
                <span class="score-label">out of 10</span>
            </div>
        </div>

        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="submitReview()">Submit & Forward</button>
        </div>
    </div>
</div>

<footer>
    <span>Project Bidding System</span> | Reviewer Portal
</footer>

<script>
    const proposals = [
        {
            id: "PROP-2024-001",
            title: "AI-Powered Customer Service Platform",
            submittedBy: "Tech Innovations Inc.",
            date: "2024-01-08",
            status: "pending",
            description: "Development of an AI-driven customer service platform with natural language processing capabilities, chatbot integration, and analytics dashboard."
        },
        {
            id: "PROP-2024-002",
            title: "Cloud Infrastructure Migration",
            submittedBy: "Digital Solutions Ltd.",
            date: "2024-01-07",
            status: "in-review",
            description: "Complete migration of legacy systems to cloud infrastructure with focus on scalability, security, and cost optimization."
        }
    ];

    let currentProposal = null;

    function loadProposals() {
        const proposalsList = document.getElementById('proposalsList');
        proposalsList.innerHTML = '';

        proposals.forEach(proposal => {
            const card = document.createElement('div');
            card.className = 'proposal-card';
            card.innerHTML = `
                <div class="proposal-header">
                    <div>
                        <div class="proposal-title">${proposal.title}</div>
                        <div class="proposal-id">${proposal.id}</div>
                    </div>
                    <span class="status-badge status-${proposal.status}">
                        ${proposal.status.replace('-', ' ')}
                    </span>
                </div>
                <div class="proposal-info">
                    <span>👤 ${proposal.submittedBy}</span>
                    <span>📅 ${proposal.date}</span>
                </div>
                <div class="proposal-description">
                    ${proposal.description}
                </div>
                <div class="proposal-actions">
                    <button class="btn btn-primary" onclick="openReviewModal('${proposal.id}')">
                        Start Review
                    </button>
                    <button class="btn btn-secondary" onclick="viewDetails('${proposal.id}')">
                        View Details
                    </button>
                </div>
            `;
            proposalsList.appendChild(card);
        });
    }

    function openReviewModal(proposalId) {
        currentProposal = proposals.find(p => p.id === proposalId);
        if (currentProposal) {
            document.getElementById('modalTitle').textContent = `Review: ${currentProposal.title}`;
            document.getElementById('reviewModal').classList.add('active');
            
            document.getElementById('reviewComments').value = '';
            document.getElementById('reviewScore').value = '';
            document.getElementById('reviewFile').value = '';
        }
    }

    function closeModal() {
        document.getElementById('reviewModal').classList.remove('active');
        currentProposal = null;
    }

    function submitReview() {
        const comments = document.getElementById('reviewComments').value;
        const score = document.getElementById('reviewScore').value;
        const file = document.getElementById('reviewFile').files[0];

        if (!comments.trim()) {
            alert('Please provide review comments');
            return;
        }

        if (!score || score < 0 || score > 10) {
            alert('Please provide a valid score between 0 and 10');
            return;
        }

        const reviewData = {
            proposalId: currentProposal.id,
            comments: comments,
            score: parseFloat(score),
            fileName: file ? file.name : null,
            reviewedAt: new Date().toISOString()
        };

        console.log('Review submitted:', reviewData);

        const proposalIndex = proposals.findIndex(p => p.id === currentProposal.id);
        if (proposalIndex !== -1) {
            proposals.splice(proposalIndex, 1);
        }

        alert(`Review submitted successfully!\n\nProposal: ${currentProposal.title}\nScore: ${score}/10\n\nThe proposal has been forwarded for next steps.`);

        closeModal();
        loadProposals();
    }

    function viewDetails(proposalId) {
        const proposal = proposals.find(p => p.id === proposalId);
        if (proposal) {
            alert(`Proposal Details:\n\nID: ${proposal.id}\nTitle: ${proposal.title}\nSubmitted By: ${proposal.submittedBy}\nDate: ${proposal.date}\n\nDescription:\n${proposal.description}`);
        }
    }

    document.getElementById('reviewModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    loadProposals();
</script>

</body>
</html>