<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Project;

Route::get('/', function (): InertiaResponse {
    return Inertia::render('welcome');
})->name('home');

// Add this diagnostic route
Route::get('/db-test', function () {
    try {
        // Test basic connectivity
        $pdo = DB::connection()->getPdo();

        // Check which schemas we can access
        $schemas = DB::select("SELECT schema_name FROM information_schema.schemata");

        // Check which tables exist in the public schema
        $tables = DB::select(query: "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
        $projects = DB::select("SELECT * FROM project");
        return [
            'connection' => 'Connected to database: ' . DB::connection()->getDatabaseName(),
            'driver' => config('database.default'),
            'schemas' => $schemas,
            'tables' => $tables,
            ''=> $projects,
        ];
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
});

// Diagnostic route to check file existence and URLs
Route::get('/file-check/{project_id}', function ($project_id) {
    $project = Project::findOrFail($project_id);

    $results = [
        'project' => [
            'id' => $project->id,
            'title' => $project->title,
            'raw_images' => $project->images,
            'raw_stl_files' => $project->stl_files,
        ],
        'image_checks' => [],
        'stl_checks' => [],
        's3_config' => [
            'driver' => config('filesystems.disks.s3.driver'),
            'bucket' => config('filesystems.disks.s3.bucket'),
            'endpoint' => config('filesystems.disks.s3.endpoint'),
            'region' => config('filesystems.disks.s3.region'),
            'key_exists' => !empty(config('filesystems.disks.s3.key')),
            'secret_exists' => !empty(config('filesystems.disks.s3.secret')),
            'url' => config('filesystems.disks.s3.url'),
        ]
    ];

    // Check images
    $images = json_decode($project->images ?? '[]', true);
    foreach ($images as $image) {
        $exists = Storage::disk('s3')->exists($image);
        $url = Storage::disk('s3')->url($image);
        $custom_url = getFileUrlDirectly($image); // Custom URL generation for comparison

        $results['image_checks'][] = [
            'path' => $image,
            'exists' => $exists,
            'url' => $url,
            'custom_url' => $custom_url,
            'size' => $exists ? Storage::disk('s3')->size($image) : null,
            'last_modified' => $exists ? Storage::disk('s3')->lastModified($image) : null,
            'mime_type' => $exists ? Storage::disk('s3')->mimeType($image) : null,
        ];
    }

    // Check STL files
    $stls = json_decode($project->stl_files ?? '[]', true);
    foreach ($stls as $stl) {
        $exists = Storage::disk('s3')->exists($stl);
        $url = Storage::disk('s3')->url($stl);
        $custom_url = getFileUrlDirectly($stl); // Custom URL generation for comparison

        $results['stl_checks'][] = [
            'path' => $stl,
            'exists' => $exists,
            'url' => $url,
            'custom_url' => $custom_url,
            'size' => $exists ? Storage::disk('s3')->size($stl) : null,
            'last_modified' => $exists ? Storage::disk('s3')->lastModified($stl) : null,
            'mime_type' => $exists ? Storage::disk('s3')->mimeType($stl) : null,
        ];
    }

    return $results;
});

// Updated R2 test route to skip listing buckets and focus on bucket operations directly
Route::get('/r2-test', function () {
    try {
        // Get credentials from config
        $access_key_id = config('filesystems.disks.s3.key');
        $access_key_secret = config('filesystems.disks.s3.secret');
        $endpoint = config('filesystems.disks.s3.endpoint');
        $bucket_name = config('filesystems.disks.s3.bucket');

        // Config info for debugging
        $results = [
            'config' => [
                'bucket' => $bucket_name,
                'endpoint' => $endpoint,
                'region' => config('filesystems.disks.s3.region'),
                'path_style' => config('filesystems.disks.s3.use_path_style_endpoint', false)
            ],
        ];

        // Create AWS credentials
        $credentials = new \Aws\Credentials\Credentials($access_key_id, $access_key_secret);

        // Configure S3 client with additional options
        $options = [
            'region' => 'auto',
            'endpoint' => $endpoint,
            'version' => 'latest',
            'credentials' => $credentials,
            'use_path_style_endpoint' => true, // Force path style for better compatibility
        ];

        // Create S3 client
        $s3_client = new \Aws\S3\S3Client($options);

        // Skip ListBuckets operation since R2 tokens often don't have this permission
        // Instead, focus directly on the bucket we're working with

        // List objects in bucket - this usually works with scoped credentials
        try {
            $contents = $s3_client->listObjectsV2([
                'Bucket' => $bucket_name,
                'MaxKeys' => 20 // Limit the number of objects to return
            ]);

            // Check if we got contents
            if (isset($contents['Contents'])) {
                // Format each object's data
                $results['bucket_contents'] = [];
                foreach ($contents['Contents'] as $object) {
                    $results['bucket_contents'][] = [
                        'key' => $object['Key'],
                        'size' => $object['Size'],
                        'last_modified' => $object['LastModified']->format('Y-m-d H:i:s'),
                        'etag' => $object['ETag'],
                        'url' => $s3_client->getObjectUrl($bucket_name, $object['Key']),
                        'url_laravel' => Storage::disk('s3')->url($object['Key']),
                    ];
                }
            } else {
                $results['bucket_contents'] = 'No objects found or empty bucket';
            }
        } catch (\Exception $e) {
            $results['list_objects_error'] = $e->getMessage();
        }

        // Test uploading a small test file
        try {
            $test_key = 'r2-test-' . time() . '.txt';
            $test_content = 'This is a test file to verify R2 uploads: ' . date('Y-m-d H:i:s');

            $upload_result = $s3_client->putObject([
                'Bucket' => $bucket_name,
                'Key' => $test_key,
                'Body' => $test_content,
                'ContentType' => 'text/plain',
            ]);

            $results['upload_test'] = [
                'success' => true,
                'key' => $test_key,
                'etag' => $upload_result['ETag'],
                'url' => $s3_client->getObjectUrl($bucket_name, $test_key)
            ];

            // Compare with Laravel's Storage facade
            $laravel_exists = Storage::disk('s3')->exists($test_key);
            $laravel_url = Storage::disk('s3')->url($test_key);

            $results['laravel_comparison'] = [
                'exists' => $laravel_exists,
                'url' => $laravel_url,
                'content' => Storage::disk('s3')->get($test_key)
            ];
        } catch (\Exception $e) {
            $results['upload_error'] = $e->getMessage();
        }

        // Test Storage facade directly
        try {
            $laravel_test_key = 'laravel-r2-test-' . time() . '.txt';
            $laravel_test_content = 'This is a test file uploaded via Laravel Storage: ' . date('Y-m-d H:i:s');

            // Upload using Laravel's Storage facade
            Storage::disk('s3')->put($laravel_test_key, $laravel_test_content);

            $results['laravel_test'] = [
                'success' => Storage::disk('s3')->exists($laravel_test_key),
                'key' => $laravel_test_key,
                'content' => Storage::disk('s3')->get($laravel_test_key),
                'url' => Storage::disk('s3')->url($laravel_test_key),
                'size' => Storage::disk('s3')->size($laravel_test_key),
                'mime' => Storage::disk('s3')->mimeType($laravel_test_key),
            ];
        } catch (\Exception $e) {
            $results['laravel_test_error'] = $e->getMessage();
        }

        return $results;

    } catch (\Exception $e) {
        return [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }
});

// Add this route to test URL generation patterns
Route::get('/url-test', function() {
    $testPaths = [
        'projects/images/example.jpg',
        'projects/models/example.stl',
        'r2-test-12345.txt',
        '/absolute/path/file.jpg',
        'https://example.com/test.jpg',
    ];

    $results = [];

    // Test Laravel Storage URLs
    foreach ($testPaths as $path) {
        $results['laravel_urls'][] = [
            'path' => $path,
            'url' => Storage::disk('s3')->url($path),
        ];
    }

    // Test custom URL function
    foreach ($testPaths as $path) {
        $results['custom_urls'][] = [
            'path' => $path,
            'url' => getFileUrlDirectly($path),
        ];
    }

    // Check if Laravel URLs and custom URLs match
    $results['match_analysis'] = [];
    foreach ($testPaths as $path) {
        $laravelUrl = Storage::disk('s3')->url($path);
        $customUrl = getFileUrlDirectly($path);

        $results['match_analysis'][] = [
            'path' => $path,
            'laravel_url' => $laravelUrl,
            'custom_url' => $customUrl,
            'urls_match' => ($laravelUrl === $customUrl),
        ];
    }

    // Analyze URL patterns
    $laravelUrlBase = preg_replace('/\/[^\/]+$/', '', Storage::disk('s3')->url('test.txt'));
    $customUrlBase = preg_replace('/\/[^\/]+$/', '', getFileUrlDirectly('test.txt'));

    $results['url_analysis'] = [
        'laravel_url_base' => $laravelUrlBase,
        'custom_url_base' => $customUrlBase,
        'config' => [
            'aws_url' => config('filesystems.disks.s3.url'),
            'aws_endpoint' => config('filesystems.disks.s3.endpoint'),
            'aws_bucket' => config('filesystems.disks.s3.bucket'),
        ]
    ];

    return $results;
});

// File proxy route - safely serves files from R2 with proper authentication
Route::get('/file/{path}', function ($path) {
    try {
        // Decode the path parameter
        $path = urldecode($path);

        // Security check - prevent directory traversal attacks
        if (strpos($path, '..') !== false || strpos($path, './') !== false) {
            abort(403, 'Invalid file path');
        }

        // Check if the file exists in the S3/R2 bucket
        if (!Storage::disk(name: 's3')->exists($path)) {
            abort(404, message: 'File not found');
        }

        // Get the file's mime type
        $mimeType = Storage::disk('s3')->mimeType($path);

        // For STL files, use the proper MIME type
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'stl') {
            $mimeType = 'application/vnd.ms-pki.stl';
        }

        // Create a temporary stream from the S3 file
        $stream = Storage::disk('s3')->readStream($path);

        // Log the access
        \Illuminate\Support\Facades\Log::info("File accessed via proxy", [
            'path' => $path,
            'mime_type' => $mimeType
        ]);

        // Return a response with the file stream
        return response()->stream(
            function () use ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            },
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to retrieve file',
            'message' => $e->getMessage()
        ], 500);
    }
})->where('path', '.*'); // Allow slashes in the path parameter

// Route to generate proxy URLs for files
Route::get('/proxy-url', function () {
    $path = request()->query('path');
    if (!$path) {
        return response()->json(['error' => 'Path parameter is required']);
    }

    $proxyUrl = url('/file/' . urlencode($path));

    // Check if file actually exists
    $exists = Storage::disk('s3')->exists($path);

    return response()->json([
        'original_path' => $path,
        'proxy_url' => $proxyUrl,
        'file_exists' => $exists,
        'direct_url' => getFileUrlDirectly($path),
        'laravel_url' => Storage::disk('s3')->url($path),
    ]);
});

// Simple HTML test page to check file access
Route::get('/file-viewer', function () {
    $path = request()->query('path');

    if (!$path) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>File Viewer</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
                form { margin-bottom: 20px; }
                input[type="text"] { width: 70%; padding: 8px; }
                button { padding: 8px 16px; background: #4CAF50; border: none; color: white; cursor: pointer; }
                .file-container { margin-top: 20px; }
                img { max-width: 100%; }
                .error { color: red; }
            </style>
        </head>
        <body>
            <h1>R2 File Viewer</h1>
            <p>Enter the path of a file in your R2 bucket:</p>
            <form method="GET">
                <input type="text" name="path" placeholder="projects/images/example.jpg" />
                <button type="submit">View File</button>
            </form>
            <p>Example paths:</p>
            <ul>
                <li>projects/images/example.jpg - for images</li>
                <li>projects/models/example.stl - for STL files</li>
            </ul>
        </body>
        </html>
        ';

        return $html;
    }

    $exists = Storage::disk('s3')->exists($path);
    $proxyUrl = url('/file/' . urlencode($path));
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    $isStl = ($extension === 'stl' || $extension === 'bin');

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>File Viewer - ' . htmlspecialchars(basename($path)) . '</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
            form { margin-bottom: 20px; }
            input[type="text"] { width: 70%; padding: 8px; }
            button { padding: 8px 16px; background: #4CAF50; border: none; color: white; cursor: pointer; }
            .file-container { margin-top: 20px; border: 1px solid #ddd; padding: 10px; }
            img { max-width: 100%; }
            .error { color: red; }
            table { width: 100%; border-collapse: collapse; }
            table td, table th { padding: 8px; border: 1px solid #ddd; }
            table th { background-color: #f2f2f2; }
        </style>
    </head>
    <body>
        <h1>R2 File Viewer</h1>
        <form method="GET">
            <input type="text" name="path" value="' . htmlspecialchars($path) . '" />
            <button type="submit">View File</button>
        </form>';

    if (!$exists) {
        $html .= '<p class="error">Error: File does not exist in the R2 bucket.</p>';
    } else {
        $html .= '
        <h2>File Information</h2>
        <table>
            <tr>
                <th>Path</th>
                <td>' . htmlspecialchars($path) . '</td>
            </tr>
            <tr>
                <th>File exists</th>
                <td>' . ($exists ? 'Yes' : 'No') . '</td>
            </tr>
            <tr>
                <th>Proxy URL</th>
                <td><a href="' . $proxyUrl . '" target="_blank">' . htmlspecialchars($proxyUrl) . '</a></td>
            </tr>
            <tr>
                <th>File type</th>
                <td>' . ($isImage ? 'Image' : ($isStl ? 'STL 3D Model' : 'Other')) . '</td>
            </tr>
        </table>

        <h2>File Preview</h2>
        <div class="file-container">';

        if ($isImage) {
            $html .= '<img src="' . $proxyUrl . '" alt="File preview" />';
        } elseif ($isStl) {
            // Simple STL viewer using Three.js
            $html .= '
            <div id="stl-viewer" style="width: 100%; height: 400px;"></div>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/three@0.124.0/examples/js/loaders/STLLoader.js"></script>
            <script>
                // Set up scene
                const scene = new THREE.Scene();
                scene.background = new THREE.Color(0xf0f0f0);

                // Set up camera
                const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
                camera.position.z = 20;

                // Set up renderer
                const renderer = new THREE.WebGLRenderer({ antialias: true });
                renderer.setSize(document.getElementById("stl-viewer").clientWidth, document.getElementById("stl-viewer").clientHeight);
                document.getElementById("stl-viewer").appendChild(renderer.domElement);

                // Add lighting
                const ambientLight = new THREE.AmbientLight(0x404040);
                scene.add(ambientLight);

                const directionalLight = new THREE.DirectionalLight(0xffffff, 0.5);
                directionalLight.position.set(0, 1, 1);
                scene.add(directionalLight);

                // Load STL file
                const loader = new THREE.STLLoader();
                loader.load("' . $proxyUrl . '", function(geometry) {
                    const material = new THREE.MeshPhongMaterial({ color: 0x00ff00, specular: 0x111111, shininess: 200 });
                    const mesh = new THREE.Mesh(geometry, material);

                    // Center the model
                    geometry.computeBoundingBox();
                    const boundingBox = geometry.boundingBox;
                    const center = new THREE.Vector3();
                    boundingBox.getCenter(center);
                    mesh.position.set(-center.x, -center.y, -center.z);

                    // Scale the model to fit the viewer
                    const size = new THREE.Vector3();
                    boundingBox.getSize(size);
                    const maxDim = Math.max(size.x, size.y, size.z);
                    const scale = 10 / maxDim;
                    mesh.scale.set(scale, scale, scale);

                    scene.add(mesh);

                    // Auto-rotate
                    function animate() {
                        requestAnimationFrame(animate);
                        mesh.rotation.y += 0.01;
                        renderer.render(scene, camera);
                    }
                    animate();
                });

                // Handle window resize
                window.addEventListener("resize", function() {
                    camera.aspect = document.getElementById("stl-viewer").clientWidth / document.getElementById("stl-viewer").clientHeight;
                    camera.updateProjectionMatrix();
                    renderer.setSize(document.getElementById("stl-viewer").clientWidth, document.getElementById("stl-viewer").clientHeight);
                });
            </script>';
        } else {
            $html .= '
            <p>This file type cannot be previewed. <a href="' . $proxyUrl . '" target="_blank">Download the file</a></p>';
        }

        $html .= '</div>';
    }

    $html .= '</body></html>';

    return $html;
});

// Helper function to generate file URLs directly (similar to frontend)
function getFileUrlDirectly(string $path): string {
    if (!$path) return '';

    $baseUrl = 'https://367be3a2035528943240074d0096e0cd.r2.cloudflarestorage.com';
    $bucket = 'fls-9e568ca0-9700-4e8f-976c-b37c71a870e6';

    if (str_starts_with($path, 'projects/')) {
        return "{$baseUrl}/{$bucket}/{$path}";
    }

    return $path;
}

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
    Route::get('tests', function () {
        return Inertia::render(component: 'tests');
    })->name('tests');
    Route::get('projectsManager', function () {
        return Inertia::render(component: 'projectsManager');
    })->name(name: 'Projects');

    // Move the projects resource route inside the auth middleware if you want it protected
    Route::resource('projectsManager', ProjectController::class);
    Route::resource('projects', ProjectController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
