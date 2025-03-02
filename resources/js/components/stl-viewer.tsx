import { useEffect, useRef } from 'react';
import { STLLoader } from 'three/examples/jsm/loaders/STLLoader';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';
import * as THREE from 'three';

// In-memory cache for the current session
const geometryCache = new Map<string, THREE.BufferGeometry>();

// Simplify the caching approach - in this version we'll focus on memory caching
// and use a simpler reliable approach for persistent storage

interface StlViewerProps {
    filepath: string;
    name: string;
    description: string;
    width?: number;
    height?: number;
}

export default function StlViewer({ filepath, name, description, width = 500, height = 400 }: StlViewerProps) {
    const containerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!containerRef.current) return;

        // Set up scene
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0x121212);

        // Add grid
        const gridHelper = new THREE.GridHelper(10, 10, 0x555555, 0x333333);
        scene.add(gridHelper);

        // Set up camera
        const camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
        camera.position.z = 5;

        // Set up renderer
        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(width, height);
        renderer.setPixelRatio(window.devicePixelRatio);
        containerRef.current.innerHTML = '';
        containerRef.current.appendChild(renderer.domElement);

        // Add lighting
        const ambientLight = new THREE.AmbientLight(0x707070, 2);
        scene.add(ambientLight);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 2);
        directionalLight.position.set(0, 1, 1);
        scene.add(directionalLight);

        const backLight = new THREE.DirectionalLight(0xffffff, 1);
        backLight.position.set(0, -1, -1);
        scene.add(backLight);

        // Add controls
        const controls = new OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.25;

        // Function to create and set up the mesh
        const setupMesh = (geometry: THREE.BufferGeometry) => {
            // Center the model
            geometry.center();

            // Create material
            const material = new THREE.MeshPhongMaterial({
                color: 0xa0c8ff,
                specular: 0x444444,
                shininess: 100,
                emissive: 0x101020,
                flatShading: false,
            });

            // Create mesh
            const mesh = new THREE.Mesh(geometry, material);

            // First determine model dimensions to ensure proper positioning
            const box = new THREE.Box3().setFromObject(mesh);
            const size = box.getSize(new THREE.Vector3()).length();
            const center = box.getCenter(new THREE.Vector3());

            // For most STL models, rotation to make them upright
            mesh.rotation.x = -Math.PI / 2;

            // Recalculate the bounding box after rotation
            mesh.updateMatrix();
            const rotatedBox = new THREE.Box3().setFromObject(mesh);
            const halfHeight = (rotatedBox.max.y - rotatedBox.min.y) / 2;

            // Position model
            mesh.position.x = -center.x;
            mesh.position.z = -center.z;
            mesh.position.y = halfHeight;

            // Adjust grid to match model size
            gridHelper.scale.set(size/5, size/5, size/5);

            // Position camera to view model properly
            camera.position.set(size, size/2, size);
            camera.lookAt(0, halfHeight, 0);

            // Add mesh to scene
            scene.add(mesh);

            // Update controls
            controls.target.set(0, halfHeight, 0);
            controls.update();

            // Animation loop
            const animate = () => {
                requestAnimationFrame(animate);
                controls.update();
                renderer.render(scene, camera);
            };

            animate();
        };

        // Check in-memory cache first
        if (geometryCache.has(filepath)) {
            console.log('Using in-memory cached geometry for:', filepath);
            setupMesh(geometryCache.get(filepath)!.clone());
            return;
        }

        // Load from server if not cached
        const loader = new STLLoader();
        loader.load(
            filepath,
            (geometry) => {
                // Store in in-memory cache
                geometryCache.set(filepath, geometry.clone());

                // Set up the mesh
                setupMesh(geometry);

                // Store in localStorage for basic persistence (just the model path as a marker)
                try {
                    localStorage.setItem(`stl-loaded-${filepath}`, 'true');
                } catch (err) {
                    console.log('Local storage not available');
                }
            },
            (xhr) => {
                console.log(`${(xhr.loaded / xhr.total) * 100}% loaded`);
            },
            (error) => {
                console.error('Error loading STL:', error);
            }
        );

        // Clean up
        return () => {
            renderer.dispose();
            scene.clear();
            if (containerRef.current) {
                containerRef.current.innerHTML = '';
            }
        };
    }, [filepath, width, height]);

    return (
        <div className="flex-shrink-0 flex flex-col rounded-lg border border-border bg-background overflow-hidden w-[400px]">
            <div
                ref={containerRef}
                className="w-[400px] h-[400px] flex items-center justify-center bg-muted"
            >
                Loading STL viewer...
            </div>
            <div className="p-4">
                <h3 className="text-xl font-bold">{name}</h3>
                <p className="text-muted-foreground text-sm">{description}</p>
            </div>
        </div>
    );
}
