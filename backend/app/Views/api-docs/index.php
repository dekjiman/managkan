<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ManagPro API Documentation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; color: #1e293b; line-height: 1.6; }
        .container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
        .header { text-align: center; margin-bottom: 48px; }
        .header h1 { font-size: 32px; font-weight: 700; margin-bottom: 8px; }
        .header p { color: #64748b; font-size: 16px; }
        .badge { display: inline-block; background: #6366f1; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-left: 8px; }
        .section { background: white; border-radius: 12px; padding: 32px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .section h2 { font-size: 20px; font-weight: 600; margin-bottom: 16px; color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; }
        .endpoint { margin-bottom: 20px; padding: 16px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #6366f1; }
        .endpoint:last-child { margin-bottom: 0; }
        .method { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-right: 8px; }
        .method.get { background: #dbeafe; color: #1d4ed8; }
        .method.post { background: #dcfce7; color: #15803d; }
        .method.patch { background: #fef3c7; color: #b45309; }
        .method.delete { background: #fee2e2; color: #dc2626; }
        .path { font-family: monospace; font-size: 14px; color: #475569; }
        .desc { margin-top: 8px; font-size: 14px; color: #64748b; }
        .auth { display: inline-block; background: #fef3c7; color: #b45309; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; margin-top: 8px; }
        .params { margin-top: 12px; }
        .params h4 { font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .params code { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
        .base-url { background: #1e293b; color: #a5f3fc; padding: 16px; border-radius: 8px; font-family: monospace; font-size: 14px; margin-bottom: 24px; }
        .base-url span { color: #94a3b8; }
        .toc { background: white; border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .toc h3 { font-size: 16px; margin-bottom: 12px; }
        .toc ul { list-style: none; }
        .toc li { margin-bottom: 8px; }
        .toc a { color: #6366f1; text-decoration: none; font-size: 14px; }
        .toc a:hover { text-decoration: underline; }
        footer { text-align: center; padding: 32px; color: #94a3b8; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ManagPro API <span class="badge">v1</span></h1>
            <p>RESTful API for project management platform</p>
        </div>

        <div class="base-url">
            <span>Base URL:</span> https://apimanagpro.matamaya.id/api
        </div>

        <div class="toc">
            <h3>Table of Contents</h3>
            <ul>
                <li><a href="#auth">Authentication</a></li>
                <li><a href="#users">Users</a></li>
                <li><a href="#workspaces">Workspaces</a></li>
                <li><a href="#boards">Boards</a></li>
                <li><a href="#cards">Cards</a></li>
                <li><a href="#comments">Comments</a></li>
                <li><a href="#members">Members</a></li>
                <li><a href="#notifications">Notifications</a></li>
                <li><a href="#billing">Billing</a></li>
                <li><a href="#apikeys">API Keys</a></li>
                <li><a href="#webhooks">Webhooks</a></li>
            </ul>
        </div>

        <!-- Authentication -->
        <div class="section" id="auth">
            <h2>Authentication</h2>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/auth/sign-up/email</span>
                <p class="desc">Register a new user account</p>
                <div class="params">
                    <h4>Body:</h4>
                    <code>name</code> (string, required) &middot;
                    <code>email</code> (string, required) &middot;
                    <code>password</code> (string, required)
                </div>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/auth/sign-in/email</span>
                <p class="desc">Login with email and password</p>
                <div class="params">
                    <h4>Body:</h4>
                    <code>email</code> (string, required) &middot;
                    <code>password</code> (string, required)
                </div>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/auth/refresh</span>
                <p class="desc">Refresh access token</p>
                <div class="params">
                    <h4>Body:</h4>
                    <code>refreshToken</code> (string, required)
                </div>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/auth/sign-in/social</span>
                <p class="desc">Get OAuth URL (Google, GitHub)</p>
                <div class="params">
                    <h4>Body:</h4>
                    <code>provider</code> (string: google|github) &middot;
                    <code>callbackURL</code> (string, optional)
                </div>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/auth/verify-email</span>
                <p class="desc">Verify email with token</p>
                <div class="params">
                    <h4>Body:</h4>
                    <code>token</code> (string, required)
                </div>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/auth/forgot-password</span>
                <p class="desc">Request password reset</p>
                <div class="params">
                    <h4>Body:</h4>
                    <code>email</code> (string, required)
                </div>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/auth/reset-password</span>
                <p class="desc">Reset password with token</p>
                <div class="params">
                    <h4>Body:</h4>
                    <code>token</code> (string, required) &middot;
                    <code>password</code> (string, required)
                </div>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/auth/logout</span>
                <p class="desc">Logout and revoke session</p>
                <span class="auth">JWT Required</span>
            </div>
        </div>

        <!-- Users -->
        <div class="section" id="users">
            <h2>Users</h2>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/users/me</span>
                <p class="desc">Get current user profile</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method patch">PATCH</span>
                <span class="path">/users/me</span>
                <p class="desc">Update current user profile</p>
                <span class="auth">JWT Required</span>
                <div class="params">
                    <h4>Body:</h4>
                    <code>name</code> (string) &middot;
                    <code>image</code> (string)
                </div>
            </div>
            <div class="endpoint">
                <span class="method delete">DELETE</span>
                <span class="path">/users/me</span>
                <p class="desc">Delete current user account</p>
                <span class="auth">JWT Required</span>
            </div>
        </div>

        <!-- Workspaces -->
        <div class="section" id="workspaces">
            <h2>Workspaces</h2>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/workspaces</span>
                <p class="desc">List all workspaces for current user</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/workspaces</span>
                <p class="desc">Create a new workspace</p>
                <span class="auth">JWT Required</span>
                <div class="params">
                    <h4>Body:</h4>
                    <code>name</code> (string, required) &middot;
                    <code>description</code> (string, optional)
                </div>
            </div>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/workspaces/{id}</span>
                <p class="desc">Get workspace details</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method patch">PATCH</span>
                <span class="path">/workspaces/{id}</span>
                <p class="desc">Update workspace</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method delete">DELETE</span>
                <span class="path">/workspaces/{id}</span>
                <p class="desc">Delete workspace (admin only)</p>
                <span class="auth">JWT Required</span>
            </div>
        </div>

        <!-- Boards -->
        <div class="section" id="boards">
            <h2>Boards</h2>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/workspaces/{id}/boards</span>
                <p class="desc">List boards in workspace</p>
                <span class="auth">JWT Required</span>
                <div class="params">
                    <h4>Query:</h4>
                    <code>type</code> (string: regular|template, optional)
                </div>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/workspaces/{id}/boards</span>
                <p class="desc">Create a new board</p>
                <span class="auth">JWT Required</span>
                <div class="params">
                    <h4>Body:</h4>
                    <code>name</code> (string, required) &middot;
                    <code>type</code> (string: regular|template) &middot;
                    <code>lists</code> (array) &middot;
                    <code>labels</code> (array)
                </div>
            </div>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/boards/{id}</span>
                <p class="desc">Get board with lists, cards, labels, members</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method patch">PATCH</span>
                <span class="path">/boards/{id}</span>
                <p class="desc">Update board</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method delete">DELETE</span>
                <span class="path">/boards/{id}</span>
                <p class="desc">Delete board</p>
                <span class="auth">JWT Required</span>
            </div>
        </div>

        <!-- Cards -->
        <div class="section" id="cards">
            <h2>Cards</h2>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/cards</span>
                <p class="desc">Create a new card</p>
                <span class="auth">JWT Required</span>
                <div class="params">
                    <h4>Body:</h4>
                    <code>title</code> (string, required) &middot;
                    <code>listPublicId</code> (string, required) &middot;
                    <code>description</code> (string) &middot;
                    <code>dueDate</code> (datetime) &middot;
                    <code>labelPublicIds</code> (array) &middot;
                    <code>memberPublicIds</code> (array)
                </div>
            </div>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/cards/{id}</span>
                <p class="desc">Get card with full details</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method patch">PATCH</span>
                <span class="path">/cards/{id}</span>
                <p class="desc">Update card</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method put">PUT</span>
                <span class="path">/cards/{id}/move</span>
                <p class="desc">Move card to another list/position</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method put">PUT</span>
                <span class="path">/cards/{id}/labels/{labelId}</span>
                <p class="desc">Toggle label on card</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method put">PUT</span>
                <span class="path">/cards/{id}/members/{memberId}</span>
                <p class="desc">Toggle member on card</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method delete">DELETE</span>
                <span class="path">/cards/{id}</span>
                <p class="desc">Delete card</p>
                <span class="auth">JWT Required</span>
            </div>
        </div>

        <!-- Comments -->
        <div class="section" id="comments">
            <h2>Comments</h2>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/comments?cardId={id}</span>
                <p class="desc">List comments for a card</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/comments</span>
                <p class="desc">Add comment to card</p>
                <span class="auth">JWT Required</span>
                <div class="params">
                    <h4>Body:</h4>
                    <code>cardId</code> (int, required) &middot;
                    <code>comment</code> (string, required) &middot;
                    <code>mentionedUserIds</code> (array, optional)
                </div>
            </div>
            <div class="endpoint">
                <span class="method patch">PATCH</span>
                <span class="path">/comments/{id}</span>
                <p class="desc">Update comment (own only)</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method delete">DELETE</span>
                <span class="path">/comments/{id}</span>
                <p class="desc">Delete comment (own only)</p>
                <span class="auth">JWT Required</span>
            </div>
        </div>

        <!-- Members -->
        <div class="section" id="members">
            <h2>Members</h2>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/workspaces/{id}/members</span>
                <p class="desc">List workspace members</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/workspaces/{id}/members</span>
                <p class="desc">Invite member to workspace</p>
                <span class="auth">JWT Required</span>
                <div class="params">
                    <h4>Body:</h4>
                    <code>email</code> (string, required) &middot;
                    <code>role</code> (string: admin|member|guest)
                </div>
            </div>
            <div class="endpoint">
                <span class="method patch">PATCH</span>
                <span class="path">/members/{id}</span>
                <p class="desc">Update member role</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method delete">DELETE</span>
                <span class="path">/members/{id}</span>
                <p class="desc">Remove member from workspace</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/members/{id}/resend-invite</span>
                <p class="desc">Resend invitation email</p>
                <span class="auth">JWT Required</span>
            </div>
        </div>

        <!-- Notifications -->
        <div class="section" id="notifications">
            <h2>Notifications</h2>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/notifications</span>
                <p class="desc">List user notifications</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/notifications/unread-count</span>
                <p class="desc">Get unread notification count</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method patch">PATCH</span>
                <span class="path">/notifications/{id}/read</span>
                <p class="desc">Mark notification as read</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method patch">PATCH</span>
                <span class="path">/notifications/read-all</span>
                <p class="desc">Mark all notifications as read</p>
                <span class="auth">JWT Required</span>
            </div>
        </div>

        <!-- Billing -->
        <div class="section" id="billing">
            <h2>Billing</h2>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/billing?workspaceSlug={slug}</span>
                <p class="desc">Get billing info for workspace</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/billing/checkout</span>
                <p class="desc">Create payment checkout</p>
                <span class="auth">JWT Required</span>
                <div class="params">
                    <h4>Body:</h4>
                    <code>planName</code> (string, required)
                </div>
            </div>
        </div>

        <!-- API Keys -->
        <div class="section" id="apikeys">
            <h2>API Keys</h2>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/api-keys?workspaceSlug={slug}</span>
                <p class="desc">List API keys for workspace</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/api-keys</span>
                <p class="desc">Create new API key</p>
                <span class="auth">JWT Required</span>
                <div class="params">
                    <h4>Body:</h4>
                    <code>name</code> (string, required) &middot;
                    <code>permissions</code> (array: read|write|admin)
                </div>
            </div>
            <div class="endpoint">
                <span class="method delete">DELETE</span>
                <span class="path">/api-keys/{id}</span>
                <p class="desc">Revoke API key</p>
                <span class="auth">JWT Required</span>
            </div>
        </div>

        <!-- Webhooks -->
        <div class="section" id="webhooks">
            <h2>Webhooks</h2>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/webhooks?workspaceSlug={slug}</span>
                <p class="desc">List webhooks for workspace</p>
                <span class="auth">JWT Required</span>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/webhooks</span>
                <p class="desc">Create webhook</p>
                <span class="auth">JWT Required</span>
                <div class="params">
                    <h4>Body:</h4>
                    <code>name</code> (string, required) &middot;
                    <code>url</code> (string, required) &middot;
                    <code>events</code> (array, required)
                </div>
            </div>
            <div class="endpoint">
                <span class="method delete">DELETE</span>
                <span class="path">/webhooks/{id}</span>
                <p class="desc">Delete webhook</p>
                <span class="auth">JWT Required</span>
            </div>
        </div>

        <footer>
            <p>ManagPro API &copy; <?= date('Y') ?> &middot; Built with CodeIgniter 4</p>
        </footer>
    </div>
</body>
</html>
