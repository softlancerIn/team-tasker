<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Team Tasker</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #6366f1;
            --accent: #8b5cf6;
            --bg-dark: #0f172a;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            background-image:
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow-x: hidden;
            padding: 2rem 0;
        }

        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            filter: blur(80px);
            border-radius: 50%;
            z-index: -1;
            opacity: 0.2;
            animation: move 20s infinite alternate;
        }

        @keyframes move {
            from {
                transform: translate(-10%, -10%);
            }

            to {
                transform: translate(10%, 10%);
            }
        }

        .auth-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-area {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.5);
        }

        .auth-title {
            color: white;
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .auth-subtitle {
            color: #94a3b8;
            font-size: 0.95rem;
        }

        .form-label {
            color: #cbd5e1;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            color: white;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            color: white;
        }

        .btn-auth {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            color: white;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
            opacity: 0.9;
        }

        .auth-footer {
            margin-top: 2rem;
            text-align: center;
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .auth-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .auth-link:hover {
            color: var(--accent);
        }

        .invalid-feedback {
            font-size: 0.8rem;
            color: #ef4444;
        }
    </style>
</head>

<body>
    <div class="blob"></div>

    <div class="container d-flex justify-content-center align-items-center min-vh-100 py-5">
        <div class="auth-card" style="max-width: 600px;">
            <div class="logo-area mb-4">
                <div class="logo-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h1 class="auth-title">Submit Enquiry</h1>
                <p class="auth-subtitle">We're here to help. Fill out the form below.</p>
            </div>

            @if (session('success'))
                <div class="alert alert-success mb-4 bg-success bg-opacity-10 border-success border-opacity-20 text-success"
                    style="border-radius: 12px; font-size: 0.85rem;">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('enquiry.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        placeholder="John Doe" required value="{{ old('name') }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        placeholder="name@example.com" required value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                        placeholder="Brief subject or topic" required value="{{ old('subject') }}">
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-control @error('priority') is-invalid @enderror" required
                        style="appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23cbd5e1%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 1rem top 50%; background-size: 0.65rem auto;">
                        <option value="low" style="background: #0f172a;"
                            {{ old('priority') == 'low' ? 'selected' : '' }}>Low - General Question</option>
                        <option value="medium" style="background: #0f172a;"
                            {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium - Need Assistance
                        </option>
                        <option value="high" style="background: #0f172a;"
                            {{ old('priority') == 'high' ? 'selected' : '' }}>High - Important Issue</option>
                        <option value="urgent" style="background: #0f172a;"
                            {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent - Critical Problem</option>
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Message</label>
                    <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="5"
                        placeholder="How can we help you today?" required>{{ old('body') }}</textarea>
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-auth mt-2">
                    Submit Enquiry <i class="fas fa-paper-plane ms-2"></i>
                </button>
            </form>

            <div class="auth-footer">
                <a href="{{ route('loginPage') }}" class="auth-link"><i class="fas fa-arrow-left me-1"></i> Back to
                    Login</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
