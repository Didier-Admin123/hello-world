pipeline {
    agent any

    environment {
        REMOTE_HOST = "192.168.0.102"
        REMOTE_USER = "jenkins"
        APP_DIR = "/opt"
        GIT_REPO = "git@github.com:Didier-Admin123/hello-world.git"
        GIT_BRANCH = "ci-cd-pipeline"
        TAR_FILE = "hello-world-${BUILD_NUMBER}.tar.gz"
        NEXUS_URL = "http://192.168.0.25:8081/repository/rar-app/"
        NEXUS_CREDENTIALS = "nexus_docker_cred"
    }

    stages {
        stage('Clone Repository on Jenkins') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        export GIT_SSH_COMMAND='ssh -o StrictHostKeyChecking=no'
                        rm -rf hello-world || true
                        git clone -b ${GIT_BRANCH} --single-branch ${GIT_REPO} hello-world
                    """
                }
            }
        }

        stage('Archive Source Code') {
            steps {
                sh """
                    tar -czvf ${TAR_FILE} hello-world
                """
            }
        }

        stage('Upload Tarball to Nexus') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'nexus_docker_cred', usernameVariable: 'NEXUS_USER', passwordVariable: 'NEXUS_PASS')]) {
                    sh """
                        curl -u "$NEXUS_USER:$NEXUS_PASS" --upload-file ${TAR_FILE} ${NEXUS_URL}${TAR_FILE}
                    """
                }
            }
        }
    }

    post {
        success {
            echo "✅ Source code successfully archived and uploaded to Nexus!"
        }
        failure {
            echo "❌ Upload failed. Check logs."
        }
    }
}
