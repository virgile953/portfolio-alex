import { useEffect, useRef, useState } from 'react';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';
import { STLLoader } from 'three/examples/jsm/loaders/STLLoader';

// In-memory cache for the current session
const geometryCache = new Map<string, THREE.BufferGeometry>();

interface StlViewerProps {
    filepath: string;
    width?: number | string;
    height?: number | string;
}

export default function StlViewer({ filepath, width = '100%', height = '100%' }: StlViewerProps) {
    const containerRef = useRef<HTMLDivElement>(null);
    const [dimensions, setDimensions] = useState({ width: 0, height: 0 });
    const rendererRef = useRef<THREE.WebGLRenderer | null>(null);
    const sceneRef = useRef<THREE.Scene | null>(null);
    const cameraRef = useRef<THREE.PerspectiveCamera | null>(null);
    const controlsRef = useRef<OrbitControls | null>(null);
    const animationFrameRef = useRef<number | null>(null);

    // Function to get container dimensions
    const updateDimensions = () => {
        if (containerRef.current) {
            const { clientWidth, clientHeight } = containerRef.current;
            setDimensions({
                width: clientWidth,
                height: clientHeight
            });

            // Update renderer size if it exists
            if (rendererRef.current && cameraRef.current) {
                rendererRef.current.setSize(clientWidth, clientHeight);
                cameraRef.current.aspect = clientWidth / clientHeight;
                cameraRef.current.updateProjectionMatrix();
            }
        }
    };

    // Initialize scene, camera, renderer
    useEffect(() => {
        // Set up scene with dark background
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0x1a1a1a); // Dark background
        sceneRef.current = scene;

        // Add grid with colors that work in dark mode
        const gridHelper = new THREE.GridHelper(10, 10, 0x555555, 0x333333);
        scene.add(gridHelper);

        // Set up initial camera (will be updated once we have dimensions)
        const camera = new THREE.PerspectiveCamera(75, 1, 0.1, 1000);
        camera.position.set(0, 3, 5);
        cameraRef.current = camera;

        // Set up renderer (will be sized later)
        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setPixelRatio(window.devicePixelRatio);
        rendererRef.current = renderer;

        // Add lighting appropriate for dark mode
        const ambientLight = new THREE.AmbientLight(0x404040, 2);
        scene.add(ambientLight);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 1.5);
        directionalLight.position.set(0, 1, 1);
        scene.add(directionalLight);

        const backLight = new THREE.DirectionalLight(0x8080ff, 0.5);
        backLight.position.set(0, -1, -1);
        scene.add(backLight);

        const rimLight = new THREE.DirectionalLight(0x404080, 0.8);
        rimLight.position.set(5, 0, -5);
        scene.add(rimLight);

        // We'll add the renderer to the DOM and setup controls after getting dimensions

        // Call once to setup
        updateDimensions();

        // Add resize listener
        window.addEventListener('resize', updateDimensions);

        return () => {
            window.removeEventListener('resize', updateDimensions);
            if (animationFrameRef.current) {
                cancelAnimationFrame(animationFrameRef.current);
            }
            if (rendererRef.current) {
                rendererRef.current.dispose();
            }
            if (sceneRef.current) {
                sceneRef.current.clear();
            }
        };
    }, []);

    // Handle dimension changes and append renderer
    useEffect(() => {
        if (!containerRef.current || !rendererRef.current || !cameraRef.current || dimensions.width === 0) return;

        // Clear container
        containerRef.current.innerHTML = '';

        // Append renderer
        containerRef.current.appendChild(rendererRef.current.domElement);

        // Update camera aspect and renderer size
        rendererRef.current.setSize(dimensions.width, dimensions.height);
        cameraRef.current.aspect = dimensions.width / dimensions.height;
        cameraRef.current.updateProjectionMatrix();

        // Setup controls
        if (controlsRef.current) {
            controlsRef.current.dispose();
        }
        controlsRef.current = new OrbitControls(cameraRef.current, rendererRef.current.domElement);
        controlsRef.current.enableDamping = true;
        controlsRef.current.dampingFactor = 0.25;
        controlsRef.current.update();

    }, [dimensions.width, dimensions.height]);

    // Handle loading the STL file
    useEffect(() => {
        if (!filepath || !sceneRef.current || !cameraRef.current || !rendererRef.current) return;

        const scene = sceneRef.current;
        const camera = cameraRef.current;
        const renderer = rendererRef.current;

        // Function to create and set up the mesh
        const setupMesh = (geometry: THREE.BufferGeometry) => {
            // Remove any existing model mesh
            const existingMesh = scene.children.find(child => child instanceof THREE.Mesh);
            if (existingMesh) {
                scene.remove(existingMesh);
            }

            // Center the model
            geometry.center();

            // Compute bounding box
            geometry.computeBoundingBox();

            // Scale model to fit view
            const boundingBox = geometry.boundingBox;
            if (boundingBox) {
                const size = new THREE.Vector3();
                boundingBox.getSize(size);
                const maxDim = Math.max(size.x, size.y, size.z);
                const scale = 5 / maxDim;
                geometry.scale(scale, scale, scale);
            }

            // Apply rotation to make the model upright
            geometry.rotateX(-Math.PI / 2);

            // Recompute bounding box after rotation
            geometry.computeBoundingBox();

            // Calculate the offset needed to place the bottom of the model on the grid
            if (boundingBox) {
                const min = boundingBox.min;
                // Translate upward by the negative of min.y to put the bottom at y=0
                geometry.translate(0, -min.y, 0);
            }

            // Create material more suitable for dark mode
            const material = new THREE.MeshPhongMaterial({
                color: 0x4a9eff,
                shininess: 100,
                specular: 0x555555,
                flatShading: false,
            });

            // Create mesh
            const mesh = new THREE.Mesh(geometry, material);

            // Add to scene
            scene.add(mesh);

            // Recalculate bounding box for camera positioning
            geometry.computeBoundingBox();
            if (geometry.boundingBox) {
                // Get the center and size of the object
                const center = new THREE.Vector3();
                const size = new THREE.Vector3();
                geometry.boundingBox.getCenter(center);
                geometry.boundingBox.getSize(size);

                // Set camera position based on the object's size
                const maxObjectSize = Math.max(size.x, size.y, size.z);
                const distance = maxObjectSize;

                // Position camera
                camera.position.set(
                    center.x + distance,
                    center.y + distance * 0.5,
                    center.z + distance
                );

                // Set controls target
                if (controlsRef.current) {
                    controlsRef.current.target.set(center.x, center.y, center.z);
                    controlsRef.current.update();
                }

                // Look at the center
                camera.lookAt(center);
            }
        };

        // Start animation loop
        const animate = () => {
            animationFrameRef.current = requestAnimationFrame(animate);
            if (controlsRef.current) {
                controlsRef.current.update();
            }
            renderer.render(scene, camera);
        };

        animate();

        // Check cache first
        if (geometryCache.has(filepath)) {
            setupMesh(geometryCache.get(filepath)!.clone());
            return;
        }

        // Load from server
        const loader = new STLLoader();
        loader.load(
            filepath, // This now comes directly as the full URL from stl_urls
            (geometry) => {
                // Cache the geometry
                geometryCache.set(filepath, geometry.clone());
                setupMesh(geometry);
            },
            undefined,
            (error) => {
                console.error('Error loading STL file:', error);

                // Show error message
                if (containerRef.current) {
                    containerRef.current.innerHTML = `
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#1a1a1a; color:#ff5555; text-align:center;">
                            <div>Failed to load 3D model:<br>${error.message || 'Unknown error'}</div>
                        </div>
                    `;
                }
            },
        );

        return () => {
            if (animationFrameRef.current) {
                cancelAnimationFrame(animationFrameRef.current);
            }
        };
    }, [filepath]);

    return (
        <div
            ref={containerRef}
            style={{
                width: width,
                height: height,
                minHeight: "400px", // Ensure a minimum height even when parent doesn't have explicit height
                position: "relative"
            }}
        />
    );
}
